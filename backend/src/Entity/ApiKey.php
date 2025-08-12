<?php

namespace App\Entity;

use App\Repository\ApiKeyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
#[ORM\Table(name: 'api_keys')]
class ApiKey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $updated_at;

    #[ORM\ManyToOne(targetEntity: Newsletter::class)]
    #[ORM\JoinColumn]
    private Newsletter $newsletter;

    #[ORM\Column(type: "string", length: 32, unique: true)]
    private string $key_hashed;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    /**
     * @var string[]
     */
    #[ORM\Column(type: "json")]
    private array $scopes = [];

    #[ORM\Column()]
    private bool $is_enabled;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $last_accessed_at = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(Newsletter $newsletter): self
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    public function getKeyHashed(): string
    {
        return $this->key_hashed;
    }

    public function setKeyHashed(string $key_hashed): self
    {
        $this->key_hashed = $key_hashed;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param string[] $scopes
     */
    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;
        return $this;
    }

    public function getIsEnabled(): bool
    {
        return $this->is_enabled;
    }

    public function setIsEnabled(bool $is_enabled): self
    {
        $this->is_enabled = $is_enabled;
        return $this;
    }

    public function getLastAccessedAt(): ?\DateTimeImmutable
    {
        return $this->last_accessed_at;
    }

    public function setLastAccessedAt(?\DateTimeImmutable $last_accessed_at): self
    {
        $this->last_accessed_at = $last_accessed_at;
        return $this;
    }
}

