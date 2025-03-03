<?php

namespace App\Api\Console\Input\Issue;

use App\Entity\Type\IssueStatus;
use Symfony\Component\Validator\Constraints as Assert;
class CreateIssueInput
{
    #[Assert\NotBlank]
    #[Assert\Type('int')]
    public int $list_id;

    public ?string $subject = null;
    public ?string $from_name = null;
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $from_email;
    #[Assert\Email]
    public ?string $reply_to_email = null;
    public ?string $content = null;
    public ?IssueStatus $status = null;
    public ?string $html = null;
    public ?string $text = null;
    public ?string $error_private = null;
    public ?int $batch_id = null;
    public ?int $scheduled_at= null;
    public ?int $sending_at = null;
    public ?int $failed_at = null;
    public ?int $sent_at = null;
}
