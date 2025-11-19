<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\EmailSenderService;
use App\Service\Issue\Message\SendEmailMessage;
use App\Service\Issue\IssueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class SendEmailMessageHandler
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private EmailSenderService     $emailSenderService,
        private MessageBusInterface    $messageBus,
    )
    {
    }

    public function __invoke(SendEmailMessage $message): void
    {
        $send = $this->em->getRepository(Send::class)->find($message->getSendId());
        assert($send !== null);
        $issue = $send->getIssue();

        try {
            $this->emailSenderService->send($issue, $send);

            $send->setStatus(SendStatus::SENT);
            $send->setSentAt($this->now());
            $this->em->flush();
        } catch (\Exception $e) {
            $attempts = $message->getAttempt();

            if ($attempts >= 4) {
                $this->em->wrapInTransaction(function () use ($send, $issue, $e) {
                    $send->setStatus(SendStatus::FAILED);
                    $send->setFailedAt($this->now());
                    $send->setErrorPrivate($e->getMessage());
                    $this->em->flush();

                    // TODO: remove this
                    $this->em->createQueryBuilder()
                        ->update(Issue::class, 'i')
                        ->set('i.failed_sends', 'i.failed_sends + 1')
                        ->where('i.id = :id')
                        ->setParameter('id', $issue->getId())
                        ->getQuery()
                        ->execute();

                    $this->em->flush();
                });

                throw new UnrecoverableMessageHandlingException(
                    'Email sending failed after 3 attempts: ' . $e->getMessage(),
                );
            } else {
                // Redispatch with exponential backoff
                // 1m, 4m, 16m
                $delaySeconds = pow(4, $attempts) * 15;

                $redispatch = new SendEmailMessage(
                    $message->getSendId(),
                    $attempts + 1
                );
                $this->messageBus->dispatch(
                    $redispatch,
                    [new DelayStamp($delaySeconds * 1000)]
                );

                // TODO: add logging here
            }
        }
    }
}
