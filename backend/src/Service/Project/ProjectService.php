<?php

namespace App\Service\Project;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService
{

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function createProject(
        string $name,
    ): Project
    {

        $project = new Project()
            ->setName($name)
            ->setUserId(1) # TODO: Replace with actual user ID
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $project;

    }

    public function deleteProject(Project $project): void
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();
    }

}
