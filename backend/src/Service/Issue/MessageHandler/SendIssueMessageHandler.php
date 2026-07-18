<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Subscriber;
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

    private const int SECONDS_PER_DAY = 86400;

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

        $intervalSeconds = $this->getIntervalSeconds($issue);

        $currentIndex = 0;

        $this->sendService->paginateSendableSubscribers(
            $issue,
            $message->getPaginationSize(),
            function (Issue $issue, Subscriber $subscriber) use (&$currentIndex, $intervalSeconds) {

                $this->sendJob(
                    $issue,
                    $subscriber,
                    $currentIndex,
                    $intervalSeconds
                );

                $currentIndex++;
            }
        );

        // The issue stays in SENDING until every email is actually sent.
        $updates = new UpdateIssueDto();
        $updates->queuedAt = $this->now();
        $this->issueService->updateIssue($issue, $updates);
    }

    /**
     * Emails are spread evenly across the day based on the newsletter's daily
     * sending rate, while never exceeding the global infrastructure throughput.
     * The interval is the number of seconds between two consecutive emails.
     */
    private function getIntervalSeconds(Issue $issue): float
    {
        $maxPerSecond = $this->appConfig->getMaxEmailsPerSecond();
        $dailyRate = $issue->getNewsletter()->getEffectiveDailySendingRate();

        return max(self::SECONDS_PER_DAY / $dailyRate, 1 / $maxPerSecond);
    }

    // this is idempotent
    private function sendJob(
        Issue      $issue,
        Subscriber $subscriber,
        int        $index,
        float      $intervalSeconds
    ): void
    {
        $delaySeconds = max($index * $intervalSeconds, 0);

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
