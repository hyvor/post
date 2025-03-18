<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\EmailTransportService;
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
        private IssueService $issueService,
        private EmailTransportService $emailTransportService,
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function __invoke(SendEmailMessage $message): void
    {

        $send = $this->em->getRepository(Send::class)->find($message->getSendId());
        assert($send !== null);
        $issue = $send->getIssue();

        try {

            // $content = $templateService->renderIssue($issue, $send);

            $this->emailTransportService->send(
                $send->getEmail(),
                (string) $issue->getSubject(),
                '<p>See Twig integration for better HTML integration!</p>'
            );

            $this->em->wrapInTransaction(function() use ($send, $issue) {
                $send->setStatus(SendStatus::SENT);
                $send->setSentAt($this->now());

                $this->em->createQuery('UPDATE App\Entity\Issue i SET i.ok_sends = i.ok_sends + 1 WHERE i.id = :id')
                    ->setParameter('id', $issue->getId())
                    ->execute();

                $this->em->flush();
                $this->checkCompletion($issue);
            });


        } catch (\Exception $e) {

            $attempts = $message->getAttempt();

            if ($attempts >= 4)
            {

                $this->em->wrapInTransaction(function() use ($send, $issue, $e) {
                    $send->setStatus(SendStatus::FAILED);
                    $send->setFailedAt($this->now());
                    $send->setErrorPrivate($e->getMessage());
                    $this->em->flush();

                    $this->em->createQueryBuilder()
                        ->update(Issue::class, 'i')
                        ->set('i.failed_sends', 'i.failed_sends + 1')
                        ->where('i.id = :id')
                        ->setParameter('id', $issue->getId())
                        ->getQuery()
                        ->execute();

                    $this->em->flush();
                    $this->checkCompletion($issue);
                });

                throw new UnrecoverableMessageHandlingException('Email sending failed after 3 attempts');
            }
            else
            {
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
            }
        }

    }

    /**
     * After any send, the issue sending job might fully complete. We check if it's the case here.
     */
    private function checkCompletion(Issue $issue): void
    {
        $this->em->refresh($issue);

        // Check if all sends are completed
        if ($issue->getOkSends() + $issue->getFailedSends() >= $issue->getTotalSends()) {
            $updates = new UpdateIssueDto();
            if ($issue->getFailedSends() > 0) {
                $updates->status = IssueStatus::FAILED;
                $updates->failedAt = $this->now();
            } else {
                $updates->status = IssueStatus::SENT;
                $updates->sentAt = $this->now();
            }

            $this->issueService->updateIssue($issue, $updates);
        }

    }
}
