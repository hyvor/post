<?php

namespace App\Service\Issue\MessageHandler;

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

    private function sendEmail(Subscriber $subscriber): void
    {
        echo('Send email to ' . $subscriber->getEmail());
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
