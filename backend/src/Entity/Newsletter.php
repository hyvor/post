<?php

namespace App\Entity;

use App\Entity\Meta\NewsletterMeta;
use App\Repository\NewsletterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterRepository::class)]
#[ORM\Table(name: 'newsletters')]
class Newsletter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(unique: true)]
    private string $subdomain;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column]
    private int $user_id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: 'json_document', options: ['jsonb' => true, 'default' => '{"#type":"newsletters_meta"}'])]
    private NewsletterMeta $meta;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: 'json')]
    private ?array $test_sent_emails = null;

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setSubdomain(string $subdomain): static
    {
        $this->subdomain = $subdomain;

        return $this;
    }

    public function getSubdomain(): string
    {
        return $this->subdomain;
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

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setMeta(NewsletterMeta $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function getMeta(): NewsletterMeta
    {
        return $this->meta;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getTestSentEmails(): ?array
    {
        return $this->test_sent_emails;
    }

    /**
     * @param string[]|null $test_sent_emails
     */
    public function setTestSentEmails(?array $test_sent_emails): static
    {
        $this->test_sent_emails = $test_sent_emails;

        return $this;
    }
}
