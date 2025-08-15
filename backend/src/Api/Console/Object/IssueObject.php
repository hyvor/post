<?php

namespace App\Api\Console\Object;

use App\Entity\Issue;
use App\Entity\Type\IssueStatus;

class IssueObject
{
    public int $id;
    public string $uuid;

    public int $created_at;
    public ?string $subject;
    public int $sending_profile_id;
    public ?string $content;
    public IssueStatus $status;
    /**
     * @var array<int>
     */
    public array $lists;
    public ?int $scheduled_at;
    public ?int $sending_at;
    public ?int $sent_at;

    public int $total_sends = 0;
    public int $opened_sends = 0;
    public int $clicked_sends = 0;

    public int $sendable_subscribers_count = 0;

    public function __construct(Issue $issue, ?int $sendableSubscribersCount = 0)
    {
        $this->id = $issue->getId();
        $this->uuid = $issue->getUuid();
        $this->created_at = $issue->getCreatedAt()->getTimestamp();
        $this->subject = $issue->getSubject();
        $this->sending_profile_id = $issue->getSendingProfile()->getId();
        $this->content = $issue->getContent();
        $this->status = $issue->getStatus();
        $this->lists = $issue->getListids();
        $this->scheduled_at = $issue->getScheduledAt()?->getTimestamp();
        $this->sending_at = $issue->getSendingAt()?->getTimestamp();
        $this->sent_at = $issue->getSentAt()?->getTimestamp();

        $this->total_sends = $issue->getTotalSends();
        $this->opened_sends = $issue->getOpenedSends();
        $this->clicked_sends = $issue->getClickedSends();

        $this->sendable_subscribers_count = $sendableSubscribersCount;
    }
}
