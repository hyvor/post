<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\EmailTransportService;
use App\Service\Issue\Message\SendJobMessage;
use App\Service\Issue\SendService;
use App\Service\Issue\IssueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use function PHPUnit\Framework\assertInstanceOf;

#[AsMessageHandler]
class SendJobMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private IssueService $issueService,
        private EmailTransportService $emailTransportService,
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

            // $content = $templateService->renderIssue($issue, $send);

            $this->emailTransportService->send(
                'test@hyvor.com',
                '<p>See Twig integration for better HTML integration!</p>'
            );

            // Update Send record
            $send->setStatus(IssueStatus::SENT);
            $send->setSentAt(new \DateTimeImmutable());
            $this->em->flush();

            // TODO: increment sentSends
            // TODO: use DB query instead
            $update = new UpdateIssueDto();
            $update->sentSends = $issue->getSentSends() + 1;
            $this->issueService->updateIssue($issue, $update);

            // TODO: $issue must be updated
            $this->checkCompletion($issue);

        } catch (\Exception $e) {
            $attempts = $message->getAttempt();

            if ($attempts > 3)
            {
                // Update Send record
                $send->setStatus(IssueStatus::FAILED);
                $send->setFailedAt(new \DateTimeImmutable());
                $this->em->flush();

                $update = new UpdateIssueDto();
                // TODO: use DB query instead
                // UPDATE issue SET failed_sends = failed_sends + 1 WHERE id = :id
                $update->failedSends = $issue->getFailedSends() + 1;
                $this->issueService->updateIssue($issue, $update);

                $this->checkCompletion($issue);

                throw new UnrecoverableMessageHandlingException('Email sending failed after 3 attempts');
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

    private function checkCompletion(Issue $issue): void
    {

        if ($issue->getSentSends() + $issue->getFailedSends() >= $issue->getTotalSends()) {

            // all the emails are sent
            // TODO: add tests
            $updates = new UpdateIssueDto();
            if ($issue->getFailedSends() > 0) {
                $updates->status = IssueStatus::FAILED;
                $updates->failedAt = new \DateTimeImmutable();
            } else {
                $updates->status = IssueStatus::SENT;
                $updates->sentAt = new \DateTimeImmutable();
            }

            $this->issueService->updateIssue($issue, $updates);

        }

    }
}
