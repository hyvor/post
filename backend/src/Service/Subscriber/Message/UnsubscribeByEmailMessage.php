<?php

namespace App\Service\Subscriber\Subscriber\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

// called after relay suppression.created webhook
// to unsubscribe all subscriber records of an email
#[AsMessage(MessageTransport::ASYNC)]
readonly class UnsubscribeByEmailMessage
{
    public function __construct(
        public string $email,
        public string $reason,
    ) {}
}
