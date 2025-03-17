<?php

namespace App\Service\Issue\Dto;

use App\Entity\Type\IssueStatus;
use App\Util\OptionalPropertyTrait;

class UpdateIssueDto
{
    use OptionalPropertyTrait;

    public ?string $subject;
    public ?string $fromName;
    /**
     * @var array<int>
     */
    public array $lists;

    public string $fromEmail;
    public ?string $replyToEmail;
    public ?string $content;

    public IssueStatus $status;
    public string $html;
    public string $text;
    public \DateTimeImmutable $sendingAt;
    public \DateTimeImmutable $sentAt;
    public \DateTimeImmutable $failedAt;

    public int $totalSends;
    public int $okSends;

    public int $failedSends;
    public string $error_private;
}
