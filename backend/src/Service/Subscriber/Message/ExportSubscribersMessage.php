<?php

namespace App\Service\Subscriber\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class ExportSubscribersMessage
{
    public function __construct(
        private int $newsletterId
    ) {
    }

    public function getNewsletterId(): int
    {
        return $this->newsletterId;
    }
}
