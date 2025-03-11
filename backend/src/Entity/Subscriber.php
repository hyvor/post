<?php

namespace App\Entity;

use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Repository\SubscriberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriberRepository::class)]
#[ORM\Table(name: 'subscribers')]
class Subscriber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\ManyToOne(inversedBy: 'subscribers')]
    #[ORM\JoinColumn(nullable: false)]
    private Project $project;

    /**
     * @var Collection<int, NewsletterList>
     */
    #[ORM\ManyToMany(targetEntity: NewsletterList::class, inversedBy: 'subscribers', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'list_subscriber')]
    #[ORM\InverseJoinColumn(name: 'list_id')]
    private Collection $lists;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(nullable: true, enumType: SubscriberStatus::class)]
    private SubscriberStatus $status;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $subscribed_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $unsubscribed_at = null;

    #[ORM\Column(enumType: SubscriberSource::class)]
    private SubscriberSource $source;

    #[ORM\Column(nullable: true)]
    private ?int $source_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subscribe_ip = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $unsubscribe_reason = null;

    /**
     * @var Collection<int, Send>
     */
    #[ORM\OneToMany(mappedBy: 'issue')]
    private Collection $sends;

    public function __construct()
    {
        $this->lists = new ArrayCollection();
    }

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

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getStatus(): SubscriberStatus
    {
        return $this->status;
    }

    public function setStatus(SubscriberStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSubscribedAt(): ?\DateTimeImmutable
    {
        return $this->subscribed_at;
    }

    public function setSubscribedAt(?\DateTimeImmutable $subscribed_at): static
    {
        $this->subscribed_at = $subscribed_at;

        return $this;
    }

    public function getUnsubscribedAt(): ?\DateTimeImmutable
    {
        return $this->unsubscribed_at;
    }

    public function setUnsubscribedAt(?\DateTimeImmutable $unsubscribed_at): static
    {
        $this->unsubscribed_at = $unsubscribed_at;

        return $this;
    }

    public function getSource(): SubscriberSource
    {
        return $this->source;
    }

    public function setSource(SubscriberSource $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getSourceId(): ?int
    {
        return $this->source_id;
    }

    public function setSourceId(?int $source_id): static
    {
        $this->source_id = $source_id;

        return $this;
    }

    public function getSubscribeIp(): ?string
    {
        return $this->subscribe_ip;
    }

    public function setSubscribeIp(?string $subscribe_ip): static
    {
        $this->subscribe_ip = $subscribe_ip;

        return $this;
    }

    public function getUnsubscribeReason(): ?string
    {
        return $this->unsubscribe_reason;
    }

    public function setUnsubscribeReason(?string $unsubscribe_reason): static
    {
        $this->unsubscribe_reason = $unsubscribe_reason;

        return $this;
    }


    /**
     * @return Collection<int, NewsletterList>
     */
    public function getLists(): Collection
    {
        return $this->lists;
    }
    public function addList(NewsletterList $list): self
    {
        if (!$this->lists->contains($list)) {
            $this->lists[] = $list;
        }
        return $this;
    }
    public function removeList(NewsletterList $list): self
    {
        $this->lists->removeElement($list);
        return $this;
    }
}
