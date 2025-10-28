<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Subscriber;
use App\Service\AppConfig;
use App\Service\Issue\Message\SendIssueMessage;
use App\Service\Issue\Message\SendEmailMessage;
use App\Service\Issue\SendService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class SendIssueMessageHandler
{
    public function __construct(
        private SendService            $sendService,
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
    }

    private function sendJob(
        Issue      $issue,
        Subscriber $subscriber,
        int        $index
    ): void
    {
        $maxPerSecond = $this->appConfig->getMaxEmailsPerSecond();
        $delaySeconds = max($index * (1 / $maxPerSecond), 0);

        $send = $this->sendService->createSend($issue, $subscriber);
        $this->bus->dispatch(
            new SendEmailMessage($send->getId()),
            [
                new DelayStamp($delaySeconds * 1000)
            ]
        );
    }
}
