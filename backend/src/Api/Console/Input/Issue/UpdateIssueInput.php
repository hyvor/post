<?php

namespace App\Api\Console\Input\Issue;

use App\Entity\Type\IssueStatus;
use App\Util\OptionalPropertyTrait;
use Symfony\Component\Validator\Constraints as Assert;
class UpdateIssueInput
{
    use OptionalPropertyTrait;

    public string $subject;
    public string $from_name;
    /**
     * @var array<int>
     */
    public array $lists;

    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $from_email;
    #[Assert\Email]
    public string $reply_to_email;
    public string $content;
    public IssueStatus $status;
    public string $html;
    public string $text;
    public string $error_private;
    public int $batch_id;
    public int $scheduled_at;
    public int $sending_at;
    public int $failed_at;
    public int $sent_at;
}
