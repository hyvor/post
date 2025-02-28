<?php

namespace App\Service\NewsletterList;

use App\Entity\NewsletterList;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NewsletterListService
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    public function createNewsletterList(
        Project $project,
        string $name,
    ): NewsletterList
    {
        $list = new NewsletterList()
            ->setProject($project)
            ->setName($name)
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now());

        $this->em->persist($list);
        $this->em->flush();

        return $list;
    }

    public function deleteNewsletterList(NewsletterList $list): void
    {
        $this->em->remove($list);
        $this->em->flush();
    }

    public function getNewsletterList(int $id): ?NewsletterList
    {
        $list = $this->em->getRepository(NewsletterList::class)->find($id);
        return $list;
    }

    /**
     * @return list<NewsletterList>
     */
    public function getNewsletterLists(Project $project): array
    {
        return $this->em->getRepository(NewsletterList::class)->findBy(['project' => $project]);
    }

    public function updateNewsletterList(NewsletterList $list, string $name): NewsletterList
    {
        $list
            ->setName($name)
            ->setUpdatedAt($this->now());

        $this->em->persist($list);
        $this->em->flush();

        return $list;
    }

    /**
     * @param array<int> $listIds
     * @return list<NewsletterList>
     */
    public function isListsAvailable(Project $project, array $listIds): array
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

        if (!empty($missingIds)) {
            throw new HttpException(422, 'Invalid list id: ' . implode(', ', $missingIds));
        }

        return $this->em->getRepository(NewsletterList::class)->findBy(['id' => $existingIds]);
    }
}
