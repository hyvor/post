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
        int $userId,
        string $name,
    ): Project
    {

        $project = new Project()
            ->setName($name)
            ->setUserId($userId)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $project->addList(
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
    public function getProjects(int $userId): array
    {
        return $this->entityManager->getRepository(Project::class)->findBy(['user_id' => $userId]);
    }
}
