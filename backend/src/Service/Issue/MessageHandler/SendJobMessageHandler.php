<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\Message\SendJobMessage;
use App\Service\Issue\SendService;
use App\Service\Issue\IssueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use function PHPUnit\Framework\assertInstanceOf;

#[AsMessageHandler]
class SendJobMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private SendService $sendService,
        private IssueService $issueService,
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function __invoke(SendJobMessage $message): void
    {
        // TODO: handle exceptions
        $issue = $this->em->getRepository(Issue::class)->find($message->getIssueId());
        assert($issue !== null);

        $send = $this->em->getRepository(Send::class)->find($message->getSendId());
        assert($send !== null);

        try {
            $this->sendService->renderAndSend($issue, $send);

            // Update Send record
            $send->setStatus(IssueStatus::SENT);
            $send->setSentAt(new \DateTimeImmutable());
            $this->em->flush();

            // Update Issue record
            $updates = new UpdateIssueDto();
            $updates->sentSends = $issue->getSentSends() + 1;

            if ($updates->sentSends === $issue->getTotalSends()) {
                $updates->status = IssueStatus::SENT;
                $updates->sentAt = new \DateTimeImmutable();
            }
            $this->issueService->updateIssue($issue, $updates);
        } catch (\Exception $e) {
            $attempts = $message->getAttempt();

            if ($attempts > 3)
            {
                // Update Send record
                $send->setStatus(IssueStatus::FAILED);
                $send->setFailedAt(new \DateTimeImmutable());
                $this->em->flush();

                throw new \Exception('Email sending failed after 3 attempts');
            }
            else
            {
                // Re-queue the message
                $redispatch = new SendJobMessage($message->getIssueId(), $message->getSendId());
                $redispatch->setAttempt($attempts + 1);
                $this->messageBus->dispatch($redispatch);
            }
        }

    }
}
