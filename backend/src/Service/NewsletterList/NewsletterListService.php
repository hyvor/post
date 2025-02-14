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
        int $projectId
    ): NewsletterList
    {
        $project = $this->entityManager->getRepository(Project::class)->find($projectId);
        if (!$project) {
            throw new \Exception('Project not found');
        }
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
    public function getNewsletterLists()
    {
        return $this->entityManager->getRepository(NewsletterList::class)->findAll();
    }


}
