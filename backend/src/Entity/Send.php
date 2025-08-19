<?php

namespace App\Entity;

use App\Entity\Type\SendStatus;
use App\Repository\SendRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SendRepository::class)]
#[ORM\Table(name: 'sends')]
class Send
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\ManyToOne(targetEntity: Newsletter::class)]
    private Newsletter $newsletter;

    #[ORM\ManyToOne(targetEntity: Issue::class)]
    private Issue $issue;

    #[ORM\ManyToOne(targetEntity: Subscriber::class)]
    private Subscriber $subscriber;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(enumType: SendStatus::class)]
    private SendStatus $status;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error_private = null;

    #[ORM\Column]
    private int $failed_tries = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sent_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $failed_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $delivered_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $unsubscribe_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $bounced_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $complained_at = null;

    #[ORM\Column(nullable: true)]
    private bool $hard_bounce = false;

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

    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(Newsletter $newsletter): static
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    public function getIssue(): Issue
    {
        return $this->issue;
    }

    public function setIssue(Issue $issue): static
    {
        $this->issue = $issue;

        return $this;
    }

    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }

    public function setSubscriber(Subscriber $subscriber): static
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getStatus(): SendStatus
    {
        return $this->status;
    }

    public function setStatus(SendStatus $status): static
    {
        $this->status = $status;

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

    public function getFailedTries(): int
    {
        return $this->failed_tries;
    }

    public function setFailedTries(int $failed_tries): static
    {
        $this->failed_tries = $failed_tries;

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

    public function getFailedAt(): ?\DateTimeImmutable
    {
        return $this->failed_at;
    }

    public function setFailedAt(?\DateTimeImmutable $failed_at): static
    {
        $this->failed_at = $failed_at;

        return $this;
    }

    public function getDeliveredAt(): ?\DateTimeImmutable
    {
        return $this->delivered_at;
    }

    public function setDeliveredAt(?\DateTimeImmutable $delivered_at): static
    {
        $this->delivered_at = $delivered_at;

        return $this;
    }

    public function getUnsubscribeAt(): ?\DateTimeImmutable
    {
        return $this->unsubscribe_at;
    }

    public function setUnsubscribeAt(?\DateTimeImmutable $unsubscribe_at): static
    {
        $this->unsubscribe_at = $unsubscribe_at;

        return $this;
    }

    public function getBouncedAt(): ?\DateTimeImmutable
    {
        return $this->bounced_at;
    }

    public function setBouncedAt(?\DateTimeImmutable $bounced_at): static
    {
        $this->bounced_at = $bounced_at;

        return $this;
    }

    public function getComplainedAt(): ?\DateTimeImmutable
    {
        return $this->complained_at;
    }

    public function setComplainedAt(?\DateTimeImmutable $complained_at): static
    {
        $this->complained_at = $complained_at;

        return $this;
    }

    public function isHardBounce(): bool
    {
        return $this->hard_bounce;
    }

    public function setHardBounce(bool $hard_bounce): static
    {
        $this->hard_bounce = $hard_bounce;

        return $this;
    }
}
