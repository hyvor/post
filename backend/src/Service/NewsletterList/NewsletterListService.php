<?php

namespace App\Service\NewsletterList;

use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Repository\IssueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NewsletterListService
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
    )
    {
    }

    public function createNewsletterList(
        Project $project,
        string $name,
        ?string $description
    ): NewsletterList
    {
        $list = new NewsletterList()
            ->setProject($project)
            ->setName($name)
            ->setDescription($description)
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now());

        $this->em->persist($list);
        $this->em->flush();

        return $list;
    }

    public function deleteNewsletterList(NewsletterList $list): void
    {
        $list->setDeletedAt($this->now());
        $this->em->persist($list);
        $this->em->flush();
    }

    public function getListById(int $id): ?NewsletterList
    {
        return $this->em->getRepository(NewsletterList::class)->find($id);
    }

    /**
     * @return ArrayCollection<int, NewsletterList>
     */
    public function getListsOfProject(Project $project): ArrayCollection
    {
        return new ArrayCollection(
            $this->em->getRepository(NewsletterList::class)
                ->findBy(
                    [
                        'project' => $project,
                        'deleted_at' => null,
                    ]
                )
        );
    }

    public function updateNewsletterList(NewsletterList $list, string $name, ?string $description): NewsletterList
    {
        $list
            ->setName($name)
            ->setDescription($description)
            ->setUpdatedAt($this->now());

        $this->em->persist($list);
        $this->em->flush();

        return $list;
    }

    /**
     * @param array<int> $listIds
     * @return ?non-empty-array<int> null if all found, otherwise, an array of missing ids
     */
    public function getMissingListIdsOfProject(Project $project, array $listIds): ?array
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('l.id')
            ->from(NewsletterList::class, 'l')
            ->where('l.project = :project')
            ->andWhere($qb->expr()->in('l.id', ':listIds'))
            ->setParameter('project', $project)
            ->setParameter('listIds', $listIds);

        $result = $qb->getQuery()->getScalarResult();

        $existingIds = array_column($result, 'id');
        $missingIds = array_diff($listIds, $existingIds);

        return count($missingIds) === 0 ? null : array_values($missingIds);
    }

    /**
     * Note that we should validate the lists are within the project (using isListsAvailable) before calling this method
     * @param array<int> $listIds
     * @return ArrayCollection<int, NewsletterList>
     */
    public function getListsByIds(array $listIds): ArrayCollection
    {
        return new ArrayCollection(
            $this->em->getRepository(NewsletterList::class)->findBy(['id' => $listIds])
        );
    }
}
