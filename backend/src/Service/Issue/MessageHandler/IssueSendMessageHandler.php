<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Subscriber;
use App\Service\Issue\Message\IssueSendMessage;
use App\Service\Issue\Message\SendJobMessage;
use App\Service\Issue\SendService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class IssueSendMessageHandler
{
    public function __construct(
        private SendService $sendService,
        private MessageBusInterface $bus
    )
    {
    }

    public function sendEmail(Issue $issue, Subscriber $subscriber): void
    {
        $send = $this->sendService->queueSend($issue, $subscriber);
        // TODO: send SendJob message
        $this->bus->dispatch(new SendJobMessage($issue, $subscriber));
    }

    public function __invoke(IssueSendMessage $message): void
    {
        // TODO: implement delay
        $this->sendService->paginateSendableSubscribers(
            $message->getIssue(),
            1000,
            function (Issue $issue, Subscriber $subscriber) {
                $this->sendEmail($issue, $subscriber);
            }
        );
    }
}
