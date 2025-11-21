<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Subscriber;
use App\Entity\Type\IssueStatus;
use App\Service\AppConfig;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\IssueService;
use App\Service\Issue\Message\SendIssueMessage;
use App\Service\Issue\Message\SendEmailMessage;
use App\Service\Issue\SendService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * TODO: This can take too long or use too much memory if there are many subscribers.
 * We need tests to make sure this works for large lists (100k+ subscribers).
 * Check symfony docs for timeouts and memory limits for message handlers.
 */
#[AsMessageHandler]
class SendIssueMessageHandler
{
    use ClockAwareTrait;

    public function __construct(
        private SendService            $sendService,
        private IssueService           $issueService,
        private MessageBusInterface    $bus,
        private EntityManagerInterface $em,
        private AppConfig              $appConfig,
    )
    {
    }

    public function __invoke(SendIssueMessage $message): void
    {
        $issue = $this->em->getRepository(Issue::class)->find($message->getIssueId());
        assert($issue !== null);

        $currentIndex = 0;

        $this->sendService->paginateSendableSubscribers(
            $issue,
            1000,
            function (Issue $issue, Subscriber $subscriber) use (&$currentIndex) {

                $this->sendJob(
                    $issue,
                    $subscriber,
                    $currentIndex
                );

                $currentIndex++;
            }
        );

        $updates = new UpdateIssueDto();
        $updates->status = IssueStatus::SENT;
        $updates->sentAt = $this->now();
        $this->issueService->updateIssue($issue, $updates);
    }

    private function sendJob(
        Issue      $issue,
        Subscriber $subscriber,
        int        $index
    ): void
    {
        $maxPerSecond = $this->appConfig->getMaxEmailsPerSecond();
        $delaySeconds = max($index * (1 / $maxPerSecond), 0);

        $this->em->wrapInTransaction(function () use ($issue, $subscriber, $delaySeconds) {
            $createdSendId = $this->sendService->createSend($issue, $subscriber);

            if ($createdSendId === false) {
                // Send already exists
                return;
            }

            $this->bus->dispatch(
                new SendEmailMessage($createdSendId),
                [
                    new DelayStamp((int)floor($delaySeconds * 1000))
                ]
            );
        });
    }
}
