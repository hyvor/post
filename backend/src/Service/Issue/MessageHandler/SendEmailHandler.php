<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Subscriber;
use App\Service\Issue\Message\SendEmailMessage;
use App\Service\Issue\SendService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailHandler
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

    public function __invoke(SendEmailMessage $message): void
    {
        dd('here');
        // TODO: Implement __invoke() method to send email
        // TODO: implement delay
        $this->sendService->paginateSendableSubscribers(
            $message->getIssue(),
            1000,
            [$this, 'sendEmail']
        );
    }
}
