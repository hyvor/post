<?php

namespace App\Entity;

use App\Entity\Type\RelayDomainStatus;
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
    private int $relay_id;

    #[ORM\Column]
    private bool $verified_in_relay = false;

    #[ORM\Column]
    private RelayDomainStatus $relay_status = RelayDomainStatus::PENDING;

    #[ORM\Column]
    private ?\DateTimeImmutable $relay_last_checked_at = null;

    #[ORM\Column]
    private ?string $relay_error_message = null;

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

    public function getRelayId(): int
    {
        return $this->relay_id;
    }

    public function setRelayId(int $relay_id): static
    {
        $this->relay_id = $relay_id;

        return $this;
    }

    public function isVerifiedInRelay(): bool
    {
        return $this->verified_in_relay;
    }

    public function setVerifiedInRelay(bool $verified_in_relay): static
    {
        $this->verified_in_relay = $verified_in_relay;

        return $this;
    }

    public function getRelayStatus(): RelayDomainStatus
    {
        return $this->relay_status;
    }

    public function setRelayStatus(RelayDomainStatus $relay_status): static
    {
        $this->relay_status = $relay_status;

        return $this;
    }

    public function getRelayLastCheckedAt(): ?\DateTimeImmutable
    {
        return $this->relay_last_checked_at;
    }

    public function setRelayLastCheckedAt(?\DateTimeImmutable $relay_last_checked_at): static
    {
        $this->relay_last_checked_at = $relay_last_checked_at;

        return $this;
    }

    public function getRelayErrorMessage(): ?string
    {
        return $this->relay_error_message;
    }

    public function setRelayErrorMessage(?string $relay_error_message): static
    {
        $this->relay_error_message = $relay_error_message;

        return $this;
    }
}
