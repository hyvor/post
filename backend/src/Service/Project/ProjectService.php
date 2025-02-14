<?php

namespace App\Service\Project;

use App\Entity\NewsletterList;
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

        $project->addNewsletterList(
            new NewsletterList()
                ->setName('Default List')
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
        );

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $project;

    }

    public function deleteProject(Project $project): void
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();
    }

    public function getProject(int $id): ?Project
    {
        $project = $this->entityManager->getRepository(Project::class)->find($id);
        return $project; // Return null if project not found
    }

    /**
     * @return list<Project>
     */
    public function getProjects()
    {
        return $this->entityManager->getRepository(Project::class)->findAll();
    }
}
