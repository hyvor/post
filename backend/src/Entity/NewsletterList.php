<?php

namespace App\Entity;

use App\Repository\ListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListRepository::class)]
#[ORM\Table(name: 'lists')]
class NewsletterList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'lists', cascade: ['persist'])]
    private Project $project;

    /**
     * @var Collection<int, Subscriber>
     */
    #[ORM\ManyToMany(targetEntity: Subscriber::class, inversedBy: 'lists', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'list_subscriber')]
    #[ORM\JoinColumn(name: 'list_id')]
    private Collection $subscribers;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column()]
    private ?string $description;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\Column()]
    private ?\DateTimeImmutable $deleted_at;

    public function __construct()
    {
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

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): static
    {
        $this->project = $project;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    /**
     * @return Collection<int, Subscriber>
     */
    public function getSubscribers(): Collection
    {
        return $this->subscribers;
    }
    public function addSubscriber(Subscriber $subscriber): self
    {
        if (!$this->subscribers->contains($subscriber)) {
            $this->subscribers[] = $subscriber;
        }
        return $this;
    }
    public function removeSubscriber(Subscriber $subscriber): self
    {
        $this->subscribers->removeElement($subscriber);
        return $this;
    }
}
