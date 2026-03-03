<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'subscriber_list_removals')]
#[ORM\UniqueConstraint(columns: ['list_id', 'subscriber_id'])]
class SubscriberListRemoval
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'list_id', nullable: false, onDelete: 'CASCADE')]
    private NewsletterList $list;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'subscriber_id', nullable: false, onDelete: 'CASCADE')]
    private Subscriber $subscriber;

    #[ORM\Column(type: 'string')]
    private string $reason;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getList(): NewsletterList
    {
        return $this->list;
    }

    public function setList(NewsletterList $list): static
    {
        $this->list = $list;
        return $this;
    }

    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }

    public function setSubscriber(Subscriber $subscriber): static
    {
        $this->subscriber = $subscriber;
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;
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
}
