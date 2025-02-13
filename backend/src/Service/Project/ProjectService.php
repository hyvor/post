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

        $project = new Project();
        $project->setName($name);
        $project->setUserId(1); # TODO: Replace with actual user ID
        $project->setCreatedAt(new \DateTimeImmutable());
        $project->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $project;

    }

    public function deleteProject(int $id)
    {
        $this->entityManager->remove($this->entityManager->find(Project::class, $id));
        $this->entityManager->flush();
    }

}
