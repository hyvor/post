<?php

namespace App\Entity;

use App\Repository\IssueRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\IssueStatus;

#[ORM\Entity(repositoryClass: IssueRepository::class)]
#[ORM\Table(name: 'issues')]
class Issue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(length: 255)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'issues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?NewsletterList $list_id = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(length: 255)]
    private ?string $from_name = null;

    #[ORM\Column(length: 255)]
    private ?string $from_email = null;

    #[ORM\Column(length: 255)]
    private ?string $reply_to_email = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column(nullable: true, enumType: IssueStatus::class)]
    private ?IssueStatus $status = null;

    #[ORM\Column(nullable: true)]
    private ?string $html = null;

    #[ORM\Column(nullable: true)]
    private ?string $text = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $scheduled_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sending_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $failed_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sent_at = null;

    #[ORM\Column(length: 255)]
    private ?string $error_private = null;

    #[ORM\Column(length:255, nullable: true)]
    private ?int $batch_id = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getListId(): ?NewsletterList
    {
        return $this->list_id;
    }

    public function setListId(?NewsletterList $list_id): static
    {
        $this->list_id = $list_id;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getFromName(): ?string
    {
        return $this->from_name;
    }

    public function setFromName(string $from_name): static
    {
        $this->from_name = $from_name;

        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->from_email;
    }

    public function setFromEmail(string $from_email): static
    {
        $this->from_email = $from_email;

        return $this;
    }

    public function getReplyToEmail(): ?string
    {
        return $this->reply_to_email;
    }

    public function setReplyToEmail(string $reply_to_email): static
    {
        $this->reply_to_email = $reply_to_email;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getStatus(): ?IssueStatus
    {
        return $this->status;
    }

    public function setStatus(?IssueStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(?string $html): static
    {
        $this->html = $html;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduled_at;
    }

    public function setScheduledAt(\DateTimeImmutable $scheduled_at): static
    {
        $this->scheduled_at = $scheduled_at;

        return $this;
    }

    public function getSendingAt(): ?\DateTimeImmutable
    {
        return $this->sending_at;
    }

    public function setSendingAt(\DateTimeImmutable $sending_at): static
    {
        $this->sending_at = $sending_at;

        return $this;
    }

    public function getFailedAt(): ?\DateTimeImmutable
    {
        return $this->failed_at;
    }

    public function setFailedAt(\DateTimeImmutable $failed_at): static
    {
        $this->failed_at = $failed_at;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sent_at;
    }

    public function setSentAt(\DateTimeImmutable $sent_at): static
    {
        $this->sent_at = $sent_at;

        return $this;
    }

    public function getErrorPrivate(): ?string
    {
        return $this->error_private;
    }

    public function setErrorPrivate(string $error_private): static
    {
        $this->error_private = $error_private;

        return $this;
    }

    public function getBatchId(): ?int
    {
        return $this->batch_id;
    }

    public function setBatchId(?int $batch_id): static
    {
        $this->batch_id = $batch_id;

        return $this;
    }
}
