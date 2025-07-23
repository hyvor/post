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
    private string $dkim_host;

    #[ORM\Column(type: Types::TEXT)]
    private string $dkim_txt_value;

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

    public function getDkimTxtvalue(): string
    {
        return $this->dkim_txt_value;
    }

    public function setDkimTxtvalue(string $dkim_txt_value): static
    {
        $this->dkim_txt_value = $dkim_txt_value;

        return $this;
    }

    public function getDkimHost(): string
    {
        return $this->dkim_host;
    }

    public function setDkimHost(string $dkim_host): static
    {
        $this->dkim_host = $dkim_host;

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
