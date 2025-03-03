<?php

namespace App\Service\Issue\Dto;

use App\Entity\Type\IssueStatus;
use App\Util\OptionalPropertyTrait;

class UpdateIssueDto
{
    use OptionalPropertyTrait;

    public string $subject;
    public string $fromName;
    /**
     * @var array<int>
     */
    public array $lists;

    public string $fromEmail;
    public string $replyToEmail;
    public string $content;
    public IssueStatus $status;
    public string $html;
    public string $text;
    public string $errorPrivate;
    public int $batchId;
    public \DateTimeImmutable $scheduledAt;
    public \DateTimeImmutable $sendingAt;
    public \DateTimeImmutable $failedAt;
    public \DateTimeImmutable $sentAt;
}
