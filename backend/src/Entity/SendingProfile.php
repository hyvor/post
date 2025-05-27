<?php

namespace App\Entity;

use App\Repository\SendingProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SendingProfileRepository::class)]
#[ORM\Table(name: 'sending_profiles')]
class SendingProfile
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

    #[ORM\ManyToOne(targetEntity: Domain::class)]
    private ?Domain $domain;

    #[ORM\Column]
    private string $from_name;

    #[ORM\Column]
    private string $from_email;

    #[ORM\Column]
    private string $reply_to_email;

    #[ORM\Column]
    private string $brand_name;

    #[ORM\Column]
    private string $brand_logo;

    #[ORM\Column]
    private bool $is_default = false;

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

    public function getDomain(): ?Domain
    {
        return $this->domain;
    }

    public function setDomain(?Domain $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function getFromName(): string
    {
        return $this->from_name;
    }

    public function setFromName(string $from_name): static
    {
        $this->from_name = $from_name;

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

    public function getReplyToEmail(): string
    {
        return $this->reply_to_email;
    }

    public function setReplyToEmail(string $reply_to_email): static
    {
        $this->reply_to_email = $reply_to_email;

        return $this;
    }

    public function getBrandName(): string
    {
        return $this->brand_name;
    }

    public function setBrandName(string $brand_name): static
    {
        $this->brand_name = $brand_name;

        return $this;
    }

    public function getBrandLogo(): string
    {
        return $this->brand_logo;
    }

    public function setBrandLogo(string $brandLogo): static
    {
        $this->brand_logo = $brandLogo;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->is_default = $isDefault;

        return $this;
    }
}
