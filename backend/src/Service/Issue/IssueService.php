<?php

namespace App\Service\Issue;

use App\Entity\NewsletterList;
use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class IssueService
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        //private IssueRepository $issueRepository
    )
    {
    }

    public function createIssue(
        NewsletterList $list,
        ?string $subject,
        ?string $fromName,
        string $fromEmail,
        ?string $replyToEmail,
        ?string $content,
        IssueStatus $status,
        ?string $html,
        ?string $text,
        ?string $error_private,
        ?int $batch_id,
        ?\DateTimeImmutable $scheduledAt = null,
        ?\DateTimeImmutable $sendingAt = null,
        ?\DateTimeImmutable $failed_at = null,
        ?\DateTimeImmutable $sent_at = null
    ): Issue
    {
        $issue = new Issue()
            ->setUuid(uniqid()) // Generate a unique identifier
            ->setList($list)
            ->setSubject($subject)
            ->setFromName($fromName)
            ->setFromEmail($fromEmail)
            ->setReplyToEmail($replyToEmail)
            ->setContent($content)
            ->setStatus($status)
            ->setHtml($html)
            ->setText($text)
            ->setErrorPrivate($error_private)
            ->setBatchId($batch_id)
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now())
            ->setScheduledAt($scheduledAt)
            ->setSendingAt($sendingAt)
            ->setFailedAt($failed_at)
            ->setSentAt($sent_at);

        if ($status === IssueStatus::SENDING) {
            $issue->setSendingAt($this->now());
        } elseif ($status === IssueStatus::FAILED) {
            $issue->setFailedAt($this->now());
        } elseif ($status === IssueStatus::SENT) {
            $issue->setSentAt($this->now());
        }

        $this->em->persist($issue);
        $this->em->flush();

        return $issue;
    }
}
