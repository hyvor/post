<?php

namespace App\Api\Public\Object\NewsletterPage;

use App\Entity\Issue;

class IssueListObject
{

    public string $uuid;
    public string $subject;
    public int $sent_at;

    public function __construct(Issue $issue)
    {
        $this->uuid = $issue->getUuid();
        $this->subject = (string)$issue->getSubject();
        $this->sent_at = ($issue->getSentAt() ?? new \DateTimeImmutable())->getTimestamp();
    }

}