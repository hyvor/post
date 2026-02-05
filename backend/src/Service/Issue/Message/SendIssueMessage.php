<?php

namespace App\Service\Issue\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class SendIssueMessage
{

    public const int PAGINATION_SIZE = 1000;

    public function __construct(
        private int $issueId,
        private int $paginationSize = self::PAGINATION_SIZE,
    ) {
    }

    public function getIssueId(): int
    {
        return $this->issueId;
    }

    public function getPaginationSize(): int
    {
        return $this->paginationSize;
    }
}
