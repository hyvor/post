<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\IssueService;
use App\Service\Issue\Message\MarkSentIssuesMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Emails of an issue are spread over time (see SendIssueMessageHandler), so an
 * issue stays in SENDING until every email has been processed. This reconciler
 * runs periodically and flips issues to SENT once fan-out finished (queued_at is
 * set) and no pending sends remain.
 */
#[AsMessageHandler]
class MarkSentIssuesMessageHandler
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private IssueService           $issueService,
    ) {
    }

    public function __invoke(MarkSentIssuesMessage $message): void
    {
        $qb = $this->em->createQueryBuilder();

        $pendingSends = $this->em->createQueryBuilder()
            ->select('s.id')
            ->from(Send::class, 's')
            ->where('s.issue = i')
            ->andWhere('s.status = :pending')
            ->getDQL();

        /** @var Issue[] $issues */
        $issues = $qb
            ->select('i')
            ->from(Issue::class, 'i')
            ->where('i.status = :sending')
            ->andWhere('i.queued_at IS NOT NULL')
            ->andWhere($qb->expr()->not($qb->expr()->exists($pendingSends)))
            ->setParameter('sending', IssueStatus::SENDING)
            ->setParameter('pending', SendStatus::PENDING)
            ->getQuery()
            ->getResult();

        foreach ($issues as $issue) {
            $updates = new UpdateIssueDto();
            $updates->status = IssueStatus::SENT;
            $updates->sentAt = $this->now();
            $this->issueService->updateIssue($issue, $updates);
        }
    }
}
