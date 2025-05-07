<?php

namespace App\Service\Project;

use App\Api\Console\Object\StatCategoryObject;
use App\Entity\Issue;
use App\Entity\Meta\ProjectMeta;
use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Entity\Subscriber;
use App\Service\Project\Dto\UpdateProjectMetaDto;
use App\Util\ClassUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Uid\Uuid;

class ProjectService
{
    use ClockAwareTrait;

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
            ->setUuid(Uuid::v4())
            ->setName($name)
            ->setUserId($userId)
            ->setMeta(new ProjectMeta())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $list = new NewsletterList()
            ->setName('Default List')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setProject($project);

        $this->em->persist($project);
        $this->em->persist($list);
        $this->em->flush();

        return $project;

    }

    public function deleteProject(Project $project): void
    {
        $this->em->remove($project);
        $this->em->flush();
    }

    public function getProjectById(int $id): ?Project
    {
        return $this->em->getRepository(Project::class)->find($id);
    }

    public function getProjectByUuid(string $uuid): ?Project
    {
        return $this->em->getRepository(Project::class)->findOneBy(['uuid' => $uuid]);
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
        $lists = (int) $this->em->getRepository(NewsletterList::class)->createQueryBuilder('l')
            ->select('count(l.id)')
            ->where('l.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();

        $listsLast30d = (int) $this->em->getRepository(NewsletterList::class)->createQueryBuilder('l')
            ->select('count(l.id)')
            ->where('l.project = :project')
            ->andWhere('l.deleted_at IS NULL')
            ->andWhere('l.created_at > :date')
            ->setParameter('project', $project)
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        $subscribers = (int) $this->em->getRepository(Subscriber::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();

        $subscribersLast30d = (int) $this->em->getRepository(Subscriber::class)->createQueryBuilder('s')
                ->select('count(s.id)')
                ->where('s.project = :project')
                ->andWhere('s.subscribed_at > :date')
                ->setParameter('project', $project)
                ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
                ->getQuery()
                ->getSingleScalarResult();

        $issues = (int) $this->em->getRepository(Issue::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();


        $issuesLast30d = (int) $this->em->getRepository(Issue::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.project = :project')
            ->andWhere('s.created_at > :date')
            ->setParameter('project', $project)
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        // TODO: return keyed values
        return [
            new StatCategoryObject($subscribers, $subscribersLast30d),
            new StatCategoryObject($issues, $issuesLast30d),
            new StatCategoryObject($lists, $listsLast30d),
        ];
    }

    public function updateProjectMeta(Project $project, UpdateProjectMetaDto $updates): Project
    {

        $currentMeta = $project->getMeta();

        foreach (get_object_vars($updates) as $property => $value) {
            $cased = new UnicodeString($property)->snake();
            $currentMeta->{$cased} = $value;
        }

        $project->setMeta(clone $currentMeta);
        $project->setUpdatedAt($this->now());

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }
}
