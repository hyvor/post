<?php

namespace App\Service\NewsletterList;

use App\Entity\NewsletterList;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class NewsletterListService
{

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function createNewsletterList(
        string $name,
        Project $project
    ): NewsletterList
    {
        $list = new NewsletterList()
            ->setName($name)
            ->setProject($project)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        return $list;
    }

    public function deleteNewsletterList(NewsletterList $list): void
    {
        $this->entityManager->remove($list);
        $this->entityManager->flush();
    }

    public function getNewsletterList(int $id): ?NewsletterList
    {
        $list = $this->entityManager->getRepository(NewsletterList::class)->find($id);
        return $list;
    }

    /**
     * @return list<NewsletterList>
     */
    public function getNewsletterLists(Project $project): array
    {
        return $this->entityManager->getRepository(NewsletterList::class)->findBy(['project' => $project]);
    }

    public function updateNewsletterList(NewsletterList $list, string $name): NewsletterList
    {
        $list
            ->setName($name)
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        return $list;
    }


}
