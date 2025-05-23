<?php

namespace App\Service\Subscriber\Message;

use App\Entity\SubscriberExport;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class ExportSubscribersMessage
{
    public function __construct(
        private int $subscriberExportId,
    ) {
    }

    public function getSubscriberExportId(): int
    {
        return $this->subscriberExportId;
    }
}
