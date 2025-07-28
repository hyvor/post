<?php

namespace App\Entity;

use App\Entity\Type\ApprovalStatus;
use App\Repository\ApprovalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApprovalRepository::class)]
#[ORM\Table(name: 'approvals')]
class Approval
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column]
    #[ORM\OneToOne(targetEntity: User::class)]
    private int $user_id;

    #[ORM\Column(nullable: false, enumType: ApprovalStatus::class)]
    private ApprovalStatus $status;
    #[ORM\Column(length: 255)]
    private string $company_name;

    #[ORM\Column(length: 255)]
    private string $country;

    #[ORM\Column(type: 'text')]
    private string $website;

    #[ORM\Column(type: 'text')]
    private ?string $social_links;

    /**
     * @var array<string, string>|null
     */
    #[ORM\Column(type: 'json')]
    private ?array $other_info;

    #[ORM\Column(length: 255)]
    private ?string $public_note;

    #[ORM\Column(length: 255)]
    private ?string $private_note;

    #[ORM\Column]
    private ?\DateTimeImmutable $approved_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $rejected_at = null;


    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setStatus(ApprovalStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): ApprovalStatus
    {
        return $this->status;
    }

    public function setCompanyName(string $company_name): static
    {
        $this->company_name = $company_name;

        return $this;
    }

    public function getCompanyName(): string
    {
        return $this->company_name;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setWebsite(string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function setSocialLinks(?string $social_links): static
    {
        $this->social_links = $social_links;

        return $this;
    }

    public function getSocialLinks(): ?string
    {
        return $this->social_links;
    }

    /**
     * @param array<string, string>|null $other_info
     */
    public function setOtherInfo(?array $other_info): static
    {
        $this->other_info = $other_info;

        return $this;
    }

    /**
     * @return array<string, string>|null
     */
    public function getOtherInfo(): ?array
    {
        return $this->other_info;
    }

    public function setPublicNote(?string $public_note): static
    {
        $this->public_note = $public_note;

        return $this;
    }

    public function getPublicNote(): ?string
    {
        return $this->public_note;
    }

    public function setPrivateNote(?string $private_note): static
    {
        $this->private_note = $private_note;

        return $this;
    }

    public function getPrivateNote(): ?string
    {
        return $this->private_note;
    }

    public function setApprovedAt(?\DateTimeImmutable $approved_at): static
    {
        $this->approved_at = $approved_at;

        return $this;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approved_at;
    }

    public function setRejectedAt(?\DateTimeImmutable $rejected_at): static
    {
        $this->rejected_at = $rejected_at;

        return $this;
    }

    public function getRejectedAt(): ?\DateTimeImmutable
    {
        return $this->rejected_at;
    }
}
