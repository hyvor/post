<?php

namespace App\Service\Issue;

use App\Entity\Issue;
use App\Entity\Newsletter;
use App\Entity\Send;
use App\Entity\Subscriber;
use App\Entity\Type\SendStatus;
use App\Entity\Type\SubscriberStatus;
use App\Repository\SendRepository;
use App\Repository\SubscriberRepository;
use App\Service\Issue\Dto\UpdateSendDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Clock\ClockAwareTrait;

class SendService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SubscriberRepository   $subscriberRepository,
        private SendRepository         $sendRepository,
    )
    {
    }

    /**
     * @return ArrayCollection<int, Send>
     */
    public function getSends(Issue $issue, int $limit, int $offset, ?string $search, ?string $sendType): ArrayCollection
    {
        $qb = $this->sendRepository->createQueryBuilder('s');

        $qb->where('s.issue = :issue')
            ->setParameter('issue', $issue)
            ->orderBy('s.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($search !== null) {
            $qb->andWhere('s.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($sendType !== null && $sendType != 'all') {
            if ($sendType === 'unsubscribed') {
                $qb->andWhere('s.unsubscribe_at IS NOT NULL');
            }
            if ($sendType === 'bounced') {
                $qb->andWhere('s.bounced_at IS NOT NULL');
            }
            if ($sendType === 'complained') {
                $qb->andWhere('s.complained_at IS NOT NULL');
            }
        }

        /** @var Send[] $results */
        $results = $qb->getQuery()->getResult();

        return new ArrayCollection($results);
    }

    public function getSendById(int $id): ?Send
    {
        return $this->sendRepository->find($id);
    }

    private function getSendableSubscribersQuery(Issue $issue): QueryBuilder
    {
        $newsletter = $issue->getNewsletter();
        $listIds = $issue->getListIds();

        return $this->subscriberRepository
            ->createQueryBuilder('s')
            ->leftJoin('s.lists', 'l')
            ->where('s.newsletter = :newsletter')
            ->andWhere('s.status = :status')
            ->andWhere('l.id IN (:listIds)')
            ->setParameter('newsletter', $newsletter)
            ->setParameter('status', SubscriberStatus::SUBSCRIBED->value)
            ->setParameter('listIds', $listIds);
    }

    public function getSendableSubscribersCount(Issue $issue): int
    {
        return (int)$this->getSendableSubscribersQuery($issue)
            ->select('COUNT(DISTINCT s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paginateSendableSubscribers(Issue $issue, int $size, callable $callback): void
    {
        $offset = 0;

        do {
            $query = $this
                ->getSendableSubscribersQuery($issue)
                ->select('s')
                ->orderBy('s.id', 'ASC')
                ->setFirstResult($offset)
                ->setMaxResults($size)
                ->getQuery();

            /** @var Subscriber[] $results */
            $results = $query->getResult();

            if (count($results) === 0) {
                break;
            }

            foreach ($results as $subscriber) {
                $callback($issue, $subscriber);

                // prevent memory leak in doctrine cache
                $this->em->detach($subscriber);
            }

            $offset += $size;
        } while (true);
    }

    public function createSend(Issue $issue, Subscriber $subscriber): int|false
    {
        $query = <<<SQL
                INSERT INTO sends (
                    issue_id, subscriber_id, newsletter_id, email,
                    status, created_at, updated_at
                ) VALUES (
                    :issue_id, :subscriber_id, :newsletter_id, :email,
                    :status, :created_at, :updated_at
                )
                ON CONFLICT (issue_id, subscriber_id) DO NOTHING
                RETURNING id
            SQL;

        $params = [
            'issue_id' => $issue->getId(),
            'subscriber_id' => $subscriber->getId(),
            'newsletter_id' => $issue->getNewsletter()->getId(),
            'email' => $subscriber->getEmail(),
            'status' => SendStatus::PENDING->value,
            'created_at' => $this->now()->format('Y-m-d H:i:s'),
            'updated_at' => $this->now()->format('Y-m-d H:i:s'),
        ];

        /** @var int|false $createdSendId */
        $createdSendId = $this->em->getConnection()->fetchOne($query, $params);

        return $createdSendId;
    }

    /**
     * @return array<string, int>|null
     */
    public function getIssueProgress(Issue $issue): ?array
    {
        $counts = $this->getIssueStats($issue);

        if ($counts['total'] === 0) {
            return null;
        }

        return [
            'total' => $counts['total'],
            'sent' => $counts['createdSends'],
            'progress' => (int)round($counts['createdSends'] / $counts['total'] * 100)
        ];
    }

    public function updateSend(Send $send, UpdateSendDto $updates): Send
    {
        if ($updates->hasProperty('status')) {
            $send->setStatus($updates->status);
        }

        if ($updates->hasProperty('deliveredAt')) {
            $send->setDeliveredAt($updates->deliveredAt);
        }

        if ($updates->hasProperty('failedAt')) {
            $send->setFailedAt($updates->failedAt);
        }

        if ($updates->hasProperty('bouncedAt')) {
            $send->setBouncedAt($updates->bouncedAt);
        }

        if ($updates->hasProperty('complainedAt')) {
            $send->setComplainedAt($updates->complainedAt);
        }

        if ($updates->hasProperty('hardBounce')) {
            $send->setHardBounce($updates->hardBounce);
        }

        $this->em->persist($send);
        $this->em->flush();

        return $send;
    }


    /**
     * @return array<string, int>
     */
    public function getIssueStats(Issue $issue, bool $full = false): array
    {
        $q = $this->em->getRepository(Send::class)->createQueryBuilder('s')
            ->where('s.issue = :issue')
            ->setParameter('issue', $issue);

        if ($full) {
            $q->select(
                'SUM(CASE WHEN s.status = :pending THEN 1 ELSE 0 END) as pendingCount',
                'SUM(CASE WHEN s.status = :sent THEN 1 ELSE 0 END) as sentCount',
                'SUM(CASE WHEN s.status = :failed THEN 1 ELSE 0 END) as failedCount',
                'SUM(CASE WHEN s.unsubscribe_at IS NOT NULL THEN 1 ELSE 0 END) as unsubscribedCount',
                'SUM(CASE WHEN s.bounced_at IS NOT NULL THEN 1 ELSE 0 END) as bouncedCount',
                'SUM(CASE WHEN s.complained_at IS NOT NULL THEN 1 ELSE 0 END) as complainedCount'
            )
                ->setParameter('pending', SendStatus::PENDING)
                ->setParameter('sent', SendStatus::SENT)
                ->setParameter('failed', SendStatus::FAILED);
        } else {
            $q->select('COUNT(s.id) as createdSendsCount');
        }

        /** @var array<string, string> $queryResults */
        $queryResults = $q->getQuery()->getSingleResult();

        $returnArray = [
            'total' => $issue->getTotalSendable(),
        ];

        if ($full) {
            $returnArray['pending'] = (int)$queryResults['pendingCount'];
            $returnArray['sent'] = (int)$queryResults['sentCount'];
            $returnArray['failed'] = (int)$queryResults['failedCount'];
            $returnArray['unsubscribed'] = (int)$queryResults['unsubscribedCount'];
            $returnArray['bounced'] = (int)$queryResults['bouncedCount'];
            $returnArray['complained'] = (int)$queryResults['complainedCount'];
        } else {
            $returnArray['createdSends'] = (int)$queryResults['createdSendsCount'];
        }

        return $returnArray;
    }

    public function getSendsCountThisMonthOfOrganization(int $organizationId): int
    {
        $query = <<<DQL
        SELECT COUNT(s.id)
        FROM App\Entity\Send s
        JOIN App\Entity\Newsletter p WITH s.newsletter = p.id
        WHERE
            p.organization_id = :organizationId AND
            s.created_at >= :startOfMonth AND
            s.created_at <= :endOfMonth
        DQL;

        $qb = $this->em->createQuery($query);
        $qb->setParameter('organizationId', $organizationId);
        $qb->setParameter('startOfMonth', $this->now()->modify('first day of this month'));
        $qb->setParameter('endOfMonth', $this->now()->modify('last day of this month'));

        return (int)$qb->getSingleScalarResult();
    }

    /**
     * @return array<string, int>
     */
    public function getSendsCountLast12MonthsOfOrganization(int $organizationId): array
    {
        $now = $this->now();
        $date12MonthsAgo = $now->modify('-11 months'); // 11 months since we have to include this month

        $query = <<<SQL
        SELECT
            to_char(sends.created_at, 'YYYY-MM') AS month,
            count(sends.id) AS count
        FROM sends
        INNER JOIN newsletters ON sends.newsletter_id = newsletters.id
        WHERE
            newsletters.organization_id = :organizationId AND
            sends.created_at >= :startDate
        GROUP BY month
        SQL;

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->bindValue('organizationId', $organizationId);
        $stmt->bindValue('startDate', $date12MonthsAgo->format('Y-m-d H:i:s'));

        /** @var array<array{month: string, count: scalar}> $results */
        $results = $stmt->executeQuery()->fetchAllAssociative();

        $indexedResults = [];
        foreach ($results as $result) {
            $indexedResults[$result['month']] = (int)$result['count'];
        }

        $formattedResults = [];

        for ($i = 0; $i < 12; $i++) {
            $date = $date12MonthsAgo->modify('+' . $i . ' month');
            $month = $date->format('Y-m');
            $formattedResults[$month] = $indexedResults[$month] ?? 0;
        }

        return $formattedResults;
    }
}
