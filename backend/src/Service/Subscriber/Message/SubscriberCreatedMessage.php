<?php

namespace App\Service\Subscriber\Message;

use App\Entity\Subscriber;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class SubscriberCreatedMessage
{
    public function __construct(
        private int $subscriberExportId,
    )
    {}

    public function getSubscriberId(): int
    {
        return $this->subscriberExportId;
    }
}
