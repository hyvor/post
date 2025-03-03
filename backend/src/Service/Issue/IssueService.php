<?php

namespace App\Service\Issue;

use App\Entity\NewsletterList;
use App\Entity\Issue;
use App\Entity\Project;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\NewsletterList\NewsletterListService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class IssueService
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private NewsletterListService $newsletterListService,
        //private IssueRepository $issueRepository
    )
    {
    }

    public function createIssueDraft(
        Project $project,
    ): Issue
    {
        $lists = $this->newsletterListService->getNewsletterLists($project);
        $list_ids = array_map(fn(NewsletterList $list) => $list->getId(), $lists);
        $issue = new Issue()
            ->setProject($project)
            ->setUuid(uniqid())
            ->setStatus(IssueStatus::DRAFT)
            ->setFromEmail('') // TODO: get from project
            ->setLists($list_ids)
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now());

        $this->em->persist($issue);
        $this->em->flush();

        return $issue;
    }
}
