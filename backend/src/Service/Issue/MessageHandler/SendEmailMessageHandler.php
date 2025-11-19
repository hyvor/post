<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Send;
use App\Entity\Type\SendStatus;
use App\Service\Issue\EmailSenderService;
use App\Service\Issue\Message\SendEmailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
        private LoggerInterface        $logger,
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
                $send->setStatus(SendStatus::FAILED);
                $send->setFailedAt($this->now());
                $send->setErrorPrivate($e->getMessage());
                $this->em->flush();

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

                $this->logger->error('Error sending email', [
                    'exception' => $e,
                    'sendId' => $send->getId(),
                    'attempts' => $attempts,
                    'reattemptInSeconds' => $delaySeconds,
                ]);
            }
        }
    }
}
