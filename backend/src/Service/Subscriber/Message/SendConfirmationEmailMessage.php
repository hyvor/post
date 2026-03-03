<?php

namespace App\Service\Subscriber\Message;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class SendConfirmationEmailMessage
{
    public function __construct(
        private int $subscriberId,
    ) {}

    public function getSubscriberId(): int
    {
        return $this->subscriberId;
    }
}
