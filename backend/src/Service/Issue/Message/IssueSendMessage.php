<?php

namespace App\Service\Issue\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class IssueSendMessage
{
    public function __construct(
        private int $issueId
    ) {
    }

    public function getIssueId(): int
    {
        return $this->issueId;
    }
}
