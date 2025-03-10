<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailHandler
{
    public function __invoke(SendEmailMessage $message)
    {
        // TODO: Implement __invoke() method to send email
        dd($message);
    }
}
