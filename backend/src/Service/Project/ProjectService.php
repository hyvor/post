<?php

namespace App\Service\Project;

use App\Api\Console\Object\StatCategoryObject;
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
        int    $userId,
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

        $this->em->persist($project);
        $this->em->flush();

        return $project;

    }

    public function deleteProject(Project $project): void
    {
        $this->em->remove($project);
        $this->em->flush();
    }

    public function getProject(Project $project): ?Project
    {
        return $project;
    }

    /**
     * @return list<Project>
     */
    public function getProjectsOfUser(int $userId): array
    {
        return $this->em->getRepository(Project::class)->findBy(['user_id' => $userId]);
    }

    /**
     * @return list<StatCategoryObject>
     */
    public function getProjectStats(Project $project): array
    {
        $lists = $this->em->getRepository(NewsletterList::class)->createQueryBuilder('l')
            ->select('count(l.id)')
            ->where('l.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();

        $listsLast30d = $this->em->getRepository(NewsletterList::class)->createQueryBuilder('l')
            ->select('count(l.id)')
            ->where('l.project = :project')
            ->andWhere('l.created_at > :date')
            ->setParameter('project', $project)
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        // TODO: return keyed values
        return [
            new StatCategoryObject(0, 0),
            new StatCategoryObject(0, 0),
            new StatCategoryObject($lists, $listsLast30d),
        ];
    }
}
