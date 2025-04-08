<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use App\Service\Template\TemplateVariables;
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

    #[ORM\OneToOne(mappedBy: 'project', cascade: ['persist'])]
    public ?Template $template = null;

    /*
    #[ORM\Column(type: 'json_document', options: ['jsonb' => true, 'default' => '{"#type":"template_variables"}'])]
    private ?TemplateVariables $variables = null;
    */

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

    public function getVariables(): TemplateVariables
    {
        return $this->variables;
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

    public function setVariables(TemplateVariables $variables): static
    {
        $this->variables = $variables;

        return $this;
    }

}
