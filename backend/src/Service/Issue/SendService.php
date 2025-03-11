<?php

namespace App\Service\Issue;

use App\Entity\Send;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Repository\IssueRepository;
use App\Entity\Issue;
use App\Repository\SubscriberRepository;
use App\Service\NewsletterList\NewsletterListService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Clock\ClockAwareTrait;

class SendService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SubscriberRepository $subscriberRepository,
    )
    {
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

    public function queueSend(Issue $issue, Subscriber $subscriber): Send
    {
        $send = new Send()
            ->setIssue($issue)
            ->setSubscriber($subscriber)
            ->setStatus($issue->getStatus())
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now());

        $this->em->persist($send);
        $this->em->flush();
//        try {
//        } catch (\Exception $e) {
//            dd($e->getMessage()); // Or use log: error_log($e->getMessage());
//        }

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
}
