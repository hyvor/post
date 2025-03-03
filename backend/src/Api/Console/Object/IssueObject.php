<?php

namespace App\Api\Console\Object;

use App\Entity\Issue;
use App\Entity\Type\IssueStatus;

class IssueObject
{
    public int $id;
    public string $uuid;

    public int $created_at;
    public string $subject;
    public string $from_name;
    public string $from_email;
    public string $reply_to_email;
    public string $content;
    public IssueStatus $status;
    /**
     * @var array<int>
     */
    public array $lists;
    public ?int $scheduled_at;
    public ?int $sending_at;
    public ?int $sent_at;

    public function __construct(Issue $issue)
    {
        $this->id = $issue->getId();
        $this->uuid = $issue->getUuid();
        $this->created_at = $issue->getCreatedAt()->getTimestamp();
        $this->subject = $issue->getSubject() ?? '';
        $this->from_name = $issue->getFromName() ?? '';
        $this->from_email = $issue->getFromEmail();
        $this->reply_to_email = $issue->getReplyToEmail() ?? '';
        $this->content = $issue->getContent() ?? '';
        $this->status = $issue->getStatus();
        $this->lists = $issue->getLists() ?? [];
        $this->scheduled_at = $issue->getScheduledAt()?->getTimestamp();
        $this->sending_at = $issue->getSendingAt()?->getTimestamp();
        $this->sent_at = $issue->getSentAt()?->getTimestamp();
    }
}
