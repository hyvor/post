<?php

namespace App\Entity;

use App\Entity\Type\SubscriberMetadataDefinitionType;
use App\Repository\SubscriberMetadataDefinitionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriberMetadataDefinitionRepository::class)]
#[ORM\Table(name: 'subscriber_metadata_definitions')]
class SubscriberMetadataDefinition
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: false)]
    private Newsletter $newsletter;

    #[ORM\Column(length: 255)]
    private string $key;

    // display name
    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(enumType: SubscriberMetadataDefinitionType::class)]
    private SubscriberMetadataDefinitionType $type;


    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(Newsletter $newsletter): void
    {
        $this->newsletter = $newsletter;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): SubscriberMetadataDefinitionType
    {
        return $this->type;
    }

    public function setType(SubscriberMetadataDefinitionType $type): void
    {
        $this->type = $type;
    }

}