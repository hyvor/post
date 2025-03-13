<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Subscriber;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\IssueService;
use App\Service\Issue\Message\IssueSendMessage;
use App\Service\Issue\Message\SendJobMessage;
use App\Service\Issue\SendService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class IssueSendMessageHandler
{
    public function __construct(
        private SendService $sendService,
        private IssueService $issueService,
        private MessageBusInterface $bus,
        private EntityManagerInterface $em,
    )
    {
    }

    public function __invoke(IssueSendMessage $message): void
    {
        $issue = $this->em->getRepository(Issue::class)->find($message->getIssueId());
        if (!$issue) {
            return;
        }

        // TODO: implement delay
        $this->sendService->paginateSendableSubscribers(
            $issue,
            1000,
            function (Issue $issue, Subscriber $subscriber) {
                $this->sendJob($issue, $subscriber);
            }
        );

        // Check after all job has been executed
        $updates = new UpdateIssueDto();
        $updates->sentSends = $issue->getSentSends() + 1;

        if ($updates->sentSends === $issue->getTotalSends()) {
            $updates->status = IssueStatus::SENT;
            $updates->sentAt = new \DateTimeImmutable();
        }
        else {
            $updates->status = IssueStatus::FAILED;
            $updates->failedAt = new \DateTimeImmutable();
        }
        $this->issueService->updateIssue($issue, $updates);
    }

    private function sendJob(Issue $issue, Subscriber $subscriber): void
    {
        $send = $this->sendService->queueSend($issue, $subscriber);
        // TODO: send SendJob message
        $this->bus->dispatch(new SendJobMessage($issue->getId(), $send->getId()));
    }
}
