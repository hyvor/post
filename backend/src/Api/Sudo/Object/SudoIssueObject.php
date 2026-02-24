<?php

namespace App\Api\Sudo\Object;

use App\Entity\Issue;
use App\Entity\Type\IssueStatus;

class SudoIssueObject
{
    public int $id;
    public int $created_at;
    public string $uuid;
    public ?string $subject;
    public IssueStatus $status;
    public string $newsletter_subdomain;
    public int $newsletter_id;
    public ?int $scheduled_at;
    public ?int $sending_at;
    public ?int $sent_at;
    public int $total_sendable;
    public ?string $error_private;

    public function __construct(Issue $issue)
    {
        $this->id = $issue->getId();
        $this->created_at = $issue->getCreatedAt()->getTimestamp();
        $this->uuid = $issue->getUuid();
        $this->subject = $issue->getSubject();
        $this->status = $issue->getStatus();
        $this->newsletter_subdomain = $issue->getNewsletter()->getSubdomain();
        $this->newsletter_id = $issue->getNewsletter()->getId();
        $this->scheduled_at = $issue->getScheduledAt()?->getTimestamp();
        $this->sending_at = $issue->getSendingAt()?->getTimestamp();
        $this->sent_at = $issue->getSentAt()?->getTimestamp();
        $this->total_sendable = $issue->getTotalSendable();
        $this->error_private = $issue->getErrorPrivate();
    }
}
