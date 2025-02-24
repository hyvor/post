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
    private string $name;

    /**
     * @var Collection<int, NewsletterList>
     */
    #[ORM\OneToMany(targetEntity: NewsletterList::class, mappedBy: 'project', cascade: ['persist'])]
    private Collection $lists;

    /**
     * @var Collection<int, Subscriber>
     */
    #[ORM\OneToMany(targetEntity: Subscriber::class, mappedBy: 'project_id')]
    private Collection $subscribers;

    public function __construct()
    {
        $this->lists = new ArrayCollection();
        $this->subscribers = new ArrayCollection();
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
     * @return Collection<int, NewsletterList>
     */
    public function getLists(): Collection
    {
        return $this->lists;
    }

    public function addList(NewsletterList $newsletterList): static
    {
        if (!$this->lists->contains($newsletterList)) {
            $this->lists->add($newsletterList);
            $newsletterList->setProject($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Subscriber>
     */
    public function getSubscribers(): Collection
    {
        return $this->subscribers;
    }

    public function addSubscriber(Subscriber $subscriber): static
    {
        if (!$this->subscribers->contains($subscriber)) {
            $this->subscribers->add($subscriber);
            $subscriber->setProject($this);
        }

        return $this;
    }
}
