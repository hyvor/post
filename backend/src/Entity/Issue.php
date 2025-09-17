<?php

namespace App\Entity;

use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueRepository::class)]
#[ORM\Table(name: 'issues')]
class Issue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column(length: 255)]
    private string $uuid;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private Newsletter $newsletter;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\ManyToOne(targetEntity: SendingProfile::class, cascade: ['persist'])]
    private SendingProfile $sending_profile;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column(enumType: IssueStatus::class)]
    private IssueStatus $status;

    #[ORM\Column(nullable: true)]
    private ?string $html = null;

    #[ORM\Column(nullable: true)]
    private ?string $text = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $scheduled_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sending_at = null;

    #[ORM\Column(length: 255)]
    private ?string $error_private = null;

    /**
     * @var array<int>
     */
    #[ORM\Column()]
    private array $list_ids;

    #[ORM\Column]
    private int $total_sends = 0;

    #[ORM\Column]
    private int $ok_sends = 0;

    #[ORM\Column]
    private int $failed_sends = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $failed_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sent_at = null;

    #[ORM\Column]
    private int $opened_sends = 0;

    #[ORM\Column]
    private int $clicked_sends = 0;

    #[ORM\Column()]
    private ?string $from_email;

    #[ORM\Column()]
    private ?string $from_name;

    #[ORM\Column()]
    private ?string $reply_to_email;
  
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(Newsletter $newsletter): static
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSendingProfile(): SendingProfile
    {
        return $this->sending_profile;
    }

    public function setSendingProfile(SendingProfile $sending_profile): static
    {
        $this->sending_profile = $sending_profile;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getStatus(): IssueStatus
    {
        return $this->status;
    }

    public function setStatus(IssueStatus $status): static
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

    public function setScheduledAt(?\DateTimeImmutable $scheduled_at): static
    {
        $this->scheduled_at = $scheduled_at;

        return $this;
    }

    public function getSendingAt(): ?\DateTimeImmutable
    {
        return $this->sending_at;
    }

    public function setSendingAt(?\DateTimeImmutable $sending_at): static
    {
        $this->sending_at = $sending_at;

        return $this;
    }

    public function getErrorPrivate(): ?string
    {
        return $this->error_private;
    }

    public function setErrorPrivate(?string $error_private): static
    {
        $this->error_private = $error_private;

        return $this;
    }

    /**
     * @return array<int>
     */
    public function getListIds(): array
    {
        return $this->list_ids;
    }

    /**
     * @param array<int> $list_ids
     */
    public function setListIds(array $list_ids): static
    {
        $this->list_ids = $list_ids;

        return $this;
    }

    public function getTotalSends(): int
    {
        return $this->total_sends;
    }

    public function setTotalSends(int $total_sends): static
    {
        $this->total_sends = $total_sends;

        return $this;
    }

    public function getOkSends(): int
    {
        return $this->ok_sends;
    }

    public function setOkSends(int $ok_sends): static
    {
        $this->ok_sends = $ok_sends;

        return $this;
    }

    public function getFailedSends(): int
    {
        return $this->failed_sends;
    }

    public function setFailedSends(int $failed_sends): static
    {
        $this->failed_sends = $failed_sends;

        return $this;
    }

    public function getFailedAt(): ?\DateTimeImmutable
    {
        return $this->failed_at;
    }

    public function setFailedAt(?\DateTimeImmutable $failed_at): static
    {
        $this->failed_at = $failed_at;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sent_at;
    }

    public function setSentAt(?\DateTimeImmutable $sent_at): static
    {
        $this->sent_at = $sent_at;

        return $this;
    }

    public function getOpenedSends(): int
    {
        return $this->opened_sends;
    }

    public function setOpenedSends(int $opened_sends): static
    {
        $this->opened_sends = $opened_sends;

        return $this;
    }

    public function getClickedSends(): int
    {
        return $this->clicked_sends;
    }

    public function setClickedSends(int $clicked_sends): static
    {
        $this->clicked_sends = $clicked_sends;

        return $this;
    }

    public function getFromEmail(): string
    {
        return $this->from_email;
    }

    public function setFromEmail(string $from_email): static
    {
        $this->from_email = $from_email;

        return $this;
    }

    public function getFromName(): ?string
    {
        return $this->from_name;
    }

    public function setFromName(?string $from_name): static
    {
        $this->from_name = $from_name;

        return $this;
    }

    public function getReplyToEmail(): ?string
    {
        return $this->reply_to_email;
    }

    public function setReplyToEmail(?string $reply_to_email): static
    {
        $this->reply_to_email = $reply_to_email;

        return $this;
    }
}
