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

class SendService
{
    public function __construct(
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

        foreach ($paginator as $subscribers) {
            $callback($subscribers);
        }

    }

    public function queueSend(Issue $issue, Subscriber $subscriber): Send
    {
        $send = new Send();
        $send->setIssue($issue);
        $send->setSubscriber($subscriber);

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
