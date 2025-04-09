<?php

namespace App\Service\Project;

use App\Api\Console\Object\StatCategoryObject;
use App\Entity\Issue;
use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Entity\Subscriber;
use App\Service\Project\Dto\UpdateProjectMetaDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

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
            ->setName($name)
            ->setUserId($userId)
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
        if ($updates->hasProperty('templateColorAccent'))
            $currentMeta->templateColorAccent = $updates->templateColorAccent;
        if ($updates->hasProperty('templateColorBackground'))
            $currentMeta->templateColorBackground = $updates->templateColorBackground;
        if ($updates->hasProperty('templateColorBoxBackground'))
            $currentMeta->templateColorBoxBackground = $updates->templateColorBoxBackground;
        if ($updates->hasProperty('templateColorBoxShadow'))
            $currentMeta->templateColorBoxShadow = $updates->templateColorBoxShadow;
        if ($updates->hasProperty('templateColorBoxBorder'))
            $currentMeta->templateColorBoxBorder = $updates->templateColorBoxBorder;
        if ($updates->hasProperty('templateFontFamily'))
            $currentMeta->templateFontFamily = $updates->templateFontFamily;
        if ($updates->hasProperty('templateFontSize'))
            $currentMeta->templateFontSize = $updates->templateFontSize;
        if ($updates->hasProperty('templateFontWeight'))
            $currentMeta->templateFontWeight = $updates->templateFontWeight;
        if ($updates->hasProperty('templateFontWeightHeading'))
            $currentMeta->templateFontWeightHeading = $updates->templateFontWeightHeading;
        if ($updates->hasProperty('templateFontColorOnBackground'))
            $currentMeta->templateFontColorOnBackground = $updates->templateFontColorOnBackground;
        if ($updates->hasProperty('templateFontColorOnBox'))
            $currentMeta->templateFontColorOnBox = $updates->templateFontColorOnBox;
        if ($updates->hasProperty('templateFontLineHeight'))
            $currentMeta->templateFontLineHeight = $updates->templateFontLineHeight;
        if ($updates->hasProperty('templateBoxRadius'))
            $currentMeta->templateBoxRadius = $updates->templateBoxRadius;
        if ($updates->hasProperty('templateLogo'))
            $currentMeta->templateLogo = $updates->templateLogo;

        $project->setMeta($currentMeta);
        $project->setUpdatedAt($this->now());

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }
}
