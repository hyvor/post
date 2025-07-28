<?php

namespace App\Service\Import\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class ImportSubscribersMessage
{
    public function __construct(
        private int $subscriberImportId,
    )
    {
    }

    public function getSubscriberImportId(): int
    {
        return $this->subscriberImportId;
    }
}
