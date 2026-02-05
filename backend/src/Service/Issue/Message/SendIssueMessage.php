<?php

namespace App\Service\Issue\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class SendIssueMessage
{
    
    public const PAGINATION_SIZE = 1000;

    public function __construct(
        private int $issueId,
        private int $paginationSize = 1000,
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
