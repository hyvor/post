<?php

namespace App\Entity;

use App\Repository\DomainRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DomainRepository::class)]
#[ORM\Table(name: 'domains')]
class Domain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column()]
    private string $domain;

    #[ORM\Column(type: Types::TEXT)]
    private string $dkim_public_key;

    #[ORM\Column(type: Types::TEXT)]
    private string $dkim_private_key;

    #[ORM\Column]
    private int $user_id;

    #[ORM\Column]
    private bool $verified_in_ses = false;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function getDkimPublicKey(): string
    {
        return $this->dkim_public_key;
    }

    public function setDkimPublicKey(string $dkim_public_key): static
    {
        $this->dkim_public_key = $dkim_public_key;

        return $this;
    }

    public function getDkimPrivateKey(): string
    {
        return $this->dkim_private_key;
    }

    public function setDkimPrivateKey(string $dkim_private_key): static
    {
        $this->dkim_private_key = $dkim_private_key;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function isVerifiedInSes(): bool
    {
        return $this->verified_in_ses;
    }

    public function setVerifiedInSes(bool $verified_in_ses): static
    {
        $this->verified_in_ses = $verified_in_ses;

        return $this;
    }
}
