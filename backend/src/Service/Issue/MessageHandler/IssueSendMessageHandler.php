<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Subscriber;
use App\Service\Issue\Message\IssueSendMessage;
use App\Service\Issue\SendService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class IssueSendMessageHandler
{
    public function __construct(
        private SendService $sendService,
    )
    {
    }

    private function sendEmail(Issue $issue, Subscriber $subscriber): void
    {
        $this->sendService->queueSend($issue, $subscriber);
    }

    public function __invoke(IssueSendMessage $message): void
    {
        // TODO: Implement __invoke() method to send email
        // TODO: implement delay
        $this->sendService->paginateSendableSubscribers(
            $message->getIssue(),
            1000,
            [$this, 'sendEmail']
        );
    }
}
