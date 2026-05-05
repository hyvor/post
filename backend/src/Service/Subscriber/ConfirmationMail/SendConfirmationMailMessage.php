<?php

namespace App\Service\Subscriber\ConfirmationMail;

use App\Service\App\Messenger\MessageTransport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(MessageTransport::ASYNC)]
readonly class SendConfirmationMailMessage
{
    public function __construct(
        private int $subscriberId,
    ) {}

    public function getSubscriberId(): int
    {
        return $this->subscriberId;
    }
}
