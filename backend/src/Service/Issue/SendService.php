<?php

namespace App\Service\Issue;

use App\Entity\Send;
use App\Entity\Subscriber;
use App\Entity\Type\SendStatus;
use App\Entity\Type\SubscriberStatus;
use App\Entity\Issue;
use App\Repository\SendRepository;
use App\Repository\SubscriberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Clock\ClockAwareTrait;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\JsonResponse;

class SendService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SubscriberRepository $subscriberRepository,
        private SendRepository $sendRepository,
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
            if ($sendType === 'opened') {
                $qb->andWhere('s.first_open_at IS NOT NULL');
            }
            if ($sendType === 'clicked') {
                $qb->andWhere('s.first_clicked_at IS NOT NULL');
            }
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

    private function getSendableSubscribersQuery(Issue $issue): QueryBuilder
    {
        $project = $issue->getProject();
        $listIds = $issue->getListIds();

        return $this->subscriberRepository
            ->createQueryBuilder('s')
            ->leftJoin('s.lists', 'l')
            ->where('s.project = :project')
            ->andWhere('s.status = :status')
            ->andWhere('l.id IN (:listIds)')
            ->setParameter('project', $project)
            ->setParameter('status', SubscriberStatus::SUBSCRIBED->value)
            ->setParameter('listIds', $listIds);
    }

    public function getSendableSubscribersCount(Issue $issue): int
    {
        return (int) $this->getSendableSubscribersQuery($issue)
            ->select('COUNT(s.id)')
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
            ->setEmail($subscriber->getEmail())
            ->setStatus(SendStatus::PENDING)
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now());

        $this->em->persist($send);
        $this->em->flush();

        return $send;
    }

   //  public function getUnsubscribedUrl()
    public function renderHtml(Issue $issue): string
    {
        // TODO: Create a proper IssueHTML class ?
        return "
            <html>
                <head>
                    <title>{$issue->getSubject()}</title>
                </head>
                <body>
                    {$issue->getContent()}
                </body>
            </html>
         ";
    }

    public function renderText(Issue $issue): string
    {
        return "
            {$issue->getSubject()}
            {$issue->getContent()}";
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
                ? (int) round($issue->getOkSends() / $issue->getTotalSends()) * 100
                : 0,
        ];
    }

}
