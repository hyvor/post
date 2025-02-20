<?php

namespace App\Service\Project;

use App\Entity\NewsletterList;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService
{

    public function __construct(
        private EntityManagerInterface $em
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

        $project->addNewsletterList(
            new NewsletterList()
                ->setName('Default List')
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
        );

        $this->em->persist($project);
        $this->em->flush();

        return $project;

    }

    public function deleteProject(Project $project): void
    {
        $this->em->remove($project);
        $this->em->flush();
    }

    public function getProject(int $id): ?Project
    {
        $project = $this->em->getRepository(Project::class)->find($id);
        return $project; // Return null if project not found
    }

    /**
     * @return list<Project>
     */
    public function getProjectsOfUser(int $userId): array
    {
        return $this->em->getRepository(Project::class)->findBy(['user_id' => $userId]);
    }
}
