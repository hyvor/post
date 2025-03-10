<?php

namespace App\MessageHandler;

use App\Entity\Issue;
use App\Entity\Subscriber;
use App\Message\SendEmailMessage;
use App\Service\Issue\SendService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailHandler
{
    private SendService $sendService;
    public function __construct(
        SendService $sendService,
    )
    {
    }

    private function sendEmail(Subscriber $subscriber): void
    {
        // TODO: send email
    }

    public function __invoke(SendEmailMessage $message): void
    {
        // TODO: Implement __invoke() method to send email
        $this->sendService->paginateSendableSubscribers(
            $message->getIssue(),
            1000,
            [$this, 'sendEmail']
        );
    }
}
