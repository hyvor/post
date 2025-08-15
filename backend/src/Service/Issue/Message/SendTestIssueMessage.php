<?php

namespace App\Service\Issue\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class SendTestIssueMessage
{
    /**
     * @param string[] $emails
     */
    public function __construct(
        private int   $issueId,
        private array $emails
    )
    {
    }

    public function getIssueId(): int
    {
        return $this->issueId;
    }

    /**
     * @return string[]
     */
    public function getEmails(): array
    {
        return $this->emails;
    }
}
