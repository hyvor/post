<?php

namespace App\Service\Import\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class ImportSubscribersMessage
{
    public function __construct(
        private int $subscriberImportId,
        /** @var int[] IDs of lists to add all imported subscribers to (in addition to the lists in the file) */
        private array $forcedListIds = [],
        /** CSV column delimiter */
        private string $delimiter = ',',
    )
    {
    }

    public function getSubscriberImportId(): int
    {
        return $this->subscriberImportId;
    }

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @return int[]
     */
    public function getForcedListIds(): array
    {
        return $this->forcedListIds;
    }
}
