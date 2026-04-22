<?php

namespace App\Service\Subscriber\Subscriber\Message;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class UnsubscribeByEmailMessageHandler
{

    public function __invoke(UnsubscribeByEmailMessage $message)
    {
        // 
    }
}
