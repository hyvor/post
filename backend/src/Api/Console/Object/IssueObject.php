<?php

namespace App\Api\Console\Object;

use App\Entity\Issue;

class IssueObject
{
    public int $id;
    public ?string $uuid;
    public string $status;
    public ?string $subject;
    public ?string $from_name;
    public ?string $from_email;
    public ?string $reply_to_email;
    public ?string $content;
    public ?string $html;
    public ?string $text;
    public ?string $error_private;
    public ?int $batch_id;
    public ?int $scheduled_at;
    public ?int $sending_at;
    public ?int $failed_at;
    public ?int $sent_at;

    public function __construct(Issue $issue)
    {
        $this->id = $issue->getId();
        $this->uuid = $issue->getUuid();
        $this->status = $issue->getStatus()->value;
        $this->subject = $issue->getSubject();
        $this->from_name = $issue->getFromName();
        $this->from_email = $issue->getFromEmail();
        $this->reply_to_email = $issue->getReplyToEmail();
        $this->content = $issue->getContent();
        $this->html = $issue->getHtml();
        $this->text = $issue->getText();
        $this->error_private = $issue->getErrorPrivate();
        $this->batch_id = $issue->getBatchId();
        $this->scheduled_at = $issue->getScheduledAt()?->getTimestamp();
        $this->sending_at = $issue->getSendingAt()?->getTimestamp();
        $this->failed_at = $issue->getFailedAt()?->getTimestamp();
        $this->sent_at = $issue->getSentAt()?->getTimestamp();
    }
}
