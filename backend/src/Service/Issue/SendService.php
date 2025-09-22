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
use App\Service\Issue\Dto\UpdateIssueDto;
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
        private IssueService           $issueService
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
        $query = $this->getSendableSubscribersQuery($issue)
            ->select('s')
            ->orderBy('s.id', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults($size)
            ->getQuery();

        $paginator = new Paginator($query);
        foreach ($paginator as $subscriber) {
            $callback($issue, $subscriber);
        }
    }

    public function createSend(Issue $issue, Subscriber $subscriber): Send
    {
        $send = new Send()
            ->setIssue($issue)
            ->setSubscriber($subscriber)
            ->setNewsletter($issue->getNewsletter())
            ->setEmail($subscriber->getEmail())
            ->setStatus(SendStatus::PENDING)
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now());

        $this->em->persist($send);
        $this->em->flush();

        return $send;
    }

    /**
     * @return array<string, int>|null
     */
    public function getIssueProgress(Issue $issue): ?array
    {
        $issueSends = $this->sendRepository->findBy(['issue' => $issue]);

        if (empty($issueSends)) {
            return null;
        }

        $pendingCount = count(array_filter($issueSends, fn(Send $send) => $send->getStatus() === SendStatus::PENDING));

        return [
            'total' => $issue->getTotalSends(),
            'pending' => $pendingCount,
            'sent' => $issue->getOkSends(),
            'progress' => $issue->getTotalSends() > 0
                ? (int)round($issue->getOkSends() / $issue->getTotalSends()) * 100
                : 0,
        ];
    }

    public function updateSend(Send $send, UpdateSendDto $updates): Send
    {
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

    public function getSendsCountThisMonthOfUser(int $hyvorUserId): int
    {
        $query = <<<DQL
        SELECT COUNT(s.id)
        FROM App\Entity\Send s
        JOIN App\Entity\Newsletter p WITH s.newsletter = p.id
        WHERE
            p.user_id = :hyvorUserId AND
            s.created_at >= :startOfMonth
        DQL;

        $qb = $this->em->createQuery($query);
        $qb->setParameter('hyvorUserId', $hyvorUserId);
        $qb->setParameter('startOfMonth', $this->now()->modify('first day of this month'));

        return (int)$qb->getSingleScalarResult();
    }

    public function getSendsCountThisMonthOfNewsletter(Newsletter $newsletter): int
    {
        $query = <<<DQL
        SELECT COUNT(s.id)
        FROM App\Entity\Send s
        WHERE
            s.newsletter = :newsletter AND
            s.created_at >= :startOfMonth AND
            s.status != 'failed'
        DQL;

        $qb = $this->em->createQuery($query);
        $qb->setParameter('newsletter', $newsletter);
        $qb->setParameter('startOfMonth', $this->now()->modify('first day of this month'));

        return (int)$qb->getSingleScalarResult();
    }

    /**
     * @return array<string, int>
     */
    public function getSendsCountLast12MonthsOfUser(int $hyvorUserId): array
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
            newsletters.user_id = :hyvorUserId AND
            sends.created_at >= :startDate
        GROUP BY month
        SQL;

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->bindValue('hyvorUserId', $hyvorUserId);
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
