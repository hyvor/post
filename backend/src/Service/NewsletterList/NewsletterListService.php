<?php

namespace App\Service\NewsletterList;

use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class NewsletterListService
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public const int MAX_LIST_DEFINITIONS_PER_PROJECT = 20;

    public function getListCounter(Newsletter $newsletter): int
    {
        return $this->em->getRepository(NewsletterList::class)
            ->count([
                'newsletter' => $newsletter,
            ]);
    }

    public function isNameAvailable(
        Newsletter $newsletter,
        string $name
    ): bool {
        return $this->em->getRepository(NewsletterList::class)
                ->count([
                    'newsletter' => $newsletter,
                    'name' => $name,
                ]) === 0;
    }

    public function createNewsletterList(
        Newsletter $newsletter,
        string $name,
        ?string $description
    ): NewsletterList {
        $list = new NewsletterList()
            ->setNewsletter($newsletter)
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
    public function getListsOfNewsletter(Newsletter $newsletter): ArrayCollection
    {
        return new ArrayCollection(
            $this->em->getRepository(NewsletterList::class)
                ->findBy(
                    [
                        'newsletter' => $newsletter,
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
    public function getMissingListIdsOfNewsletter(Newsletter $newsletter, array $listIds): ?array
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('l.id')
            ->from(NewsletterList::class, 'l')
            ->where('l.newsletter = :newsletter')
            ->andWhere($qb->expr()->in('l.id', ':listIds'))
            ->setParameter('newsletter', $newsletter)
            ->setParameter('listIds', $listIds);

        $result = $qb->getQuery()->getScalarResult();

        $existingIds = array_column($result, 'id');
        $missingIds = array_diff($listIds, $existingIds);

        return count($missingIds) === 0 ? null : array_values($missingIds);
    }

    /**
     * Note that we should validate the lists are within the newsletter (using isListsAvailable) before calling this method
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
