<?php

namespace App\Entity;

use App\Entity\Type\SubscriberStatus;
use App\Entity\Type\UserRole;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'hyvor_user_id')]
    #[ORM\JoinColumn(nullable: false)]
    private Project $project;

    #[ORM\Column]
    private int $hyvor_user_id;

    #[ORM\Column(nullable: false, enumType: UserRole::class)]
    private UserRole $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getProjectId(): Project
    {
        return $this->project;
    }

    public function setProjectId(Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getHyvorUserId(): ?int
    {
        return $this->hyvor_user_id;
    }

    public function setHyvorUserId(int $hyvor_user_id): static
    {
        $this->hyvor_user_id = $hyvor_user_id;

        return $this;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;

        return $this;
    }
}
