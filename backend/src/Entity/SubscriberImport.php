<?php

namespace App\Entity;

use App\Entity\Type\SubscriberImportStatus;
use App\Repository\SubscriberImportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriberImportRepository::class)]
#[ORM\Table(name: 'subscriber_imports')]
class SubscriberImport
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: false)]
    private Newsletter $newsletter;

    #[ORM\OneToOne()]
    #[ORM\JoinColumn(nullable: false)]
    private Media $media;

    #[ORM\Column(type: 'string', enumType: SubscriberImportStatus::class)]
    private SubscriberImportStatus $status;

    /**
     * @var array<string, string|null> | null
     */
    #[ORM\Column(type: 'json')]
    private ?array $fields = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $imported_subscribers = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $error_message = null;


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

    public function getMedia(): Media
    {
        return $this->media;
    }

    public function setMedia(Media $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getStatus(): SubscriberImportStatus
    {
        return $this->status;
    }

    public function setStatus(SubscriberImportStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return array<string, string|null> | null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @param array<string, string|null> | null $fields
     */
    public function setFields(?array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    public function getImportedSubscribers(): ?int
    {
        return $this->imported_subscribers;
    }

    public function setImportedSubscribers(?int $imported_subscribers): static
    {
        $this->imported_subscribers = $imported_subscribers;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->error_message;
    }

    public function setErrorMessage(?string $error_message): static
    {
        $this->error_message = $error_message;

        return $this;
    }

}
