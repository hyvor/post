<?php

namespace App\Service\Issue;

use App\Entity\NewsletterList;
use App\Entity\Issue;
use App\Entity\Project;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\Dto\UpdateIssueDto;
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

    public function updateIssue(Issue $issue, UpdateIssueDto $updates): Issue
    {
        if ($updates->hasProperty('subject')) {
            $issue->setSubject($updates->subject);
        }

        if ($updates->hasProperty('fromName')) {
            $issue->setFromName($updates->fromName);
        }

        if ($updates->hasProperty('fromEmail')) {
            $issue->setFromEmail($updates->fromEmail);
        }

        if ($updates->hasProperty('replyToEmail')) {
            $issue->setReplyToEmail($updates->replyToEmail);
        }

        if ($updates->hasProperty('content')) {
            $issue->setContent($updates->content);
        }

        if ($updates->hasProperty('status')) {
            $issue = $issue->setStatus($updates->status);
            if ($updates->status === IssueStatus::SENDING) {
                $issue->setSendingAt($this->now());
            } elseif ($updates->status === IssueStatus::FAILED) {
                $issue->setFailedAt($this->now());
            } elseif ($updates->status === IssueStatus::SENT) {
                $issue->setSentAt($this->now());
            }
        }

        if ($updates->hasProperty('html')) {
            $issue->setHtml($updates->html);
        }

        if ($updates->hasProperty('text')) {
            $issue->setText($updates->text);
        }

        if ($updates->hasProperty('errorPrivate')) {
            $issue->setErrorPrivate($updates->errorPrivate);
        }

        if ($updates->hasProperty('batchId')) {
            $issue->setBatchId($updates->batchId);
        }

        if ($updates->hasProperty('scheduledAt')) {
            $issue->setScheduledAt($updates->scheduledAt);
        }

        if ($updates->hasProperty('sendingAt')) {
            $issue->setSendingAt($updates->sendingAt);
        }

        if ($updates->hasProperty('failedAt')) {
            $issue->setFailedAt($updates->failedAt);
        }

        if ($updates->hasProperty('sentAt')) {
            $issue->setSentAt($updates->sentAt);
        }

        $issue->setUpdatedAt($this->now());


        $this->em->persist($issue);
        $this->em->flush();

        return $issue;
    }
}
