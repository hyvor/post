<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'projects')]
class Project
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
    private ?int $user_id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, NewsletterList>
     */
    #[ORM\OneToMany(targetEntity: NewsletterList::class, mappedBy: 'project_id')]
    private Collection $newsletterLists;

    public function __construct()
    {
        $this->newsletterLists = new ArrayCollection();
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, NewsletterList>
     */
    public function getNewsletterLists(): Collection
    {
        return $this->newsletterLists;
    }

    public function addNewsletterList(NewsletterList $newsletterList): static
    {
        if (!$this->newsletterLists->contains($newsletterList)) {
            $this->newsletterLists->add($newsletterList);
            $newsletterList->setProjectId($this);
        }

        return $this;
    }

    public function removeNewsletterList(NewsletterList $newsletterList): static
    {
        if ($this->newsletterLists->removeElement($newsletterList)) {
            // set the owning side to null (unless already changed)
            if ($newsletterList->getProjectId() === $this) {
                $newsletterList->setProjectId(null);
            }
        }

        return $this;
    }
}
