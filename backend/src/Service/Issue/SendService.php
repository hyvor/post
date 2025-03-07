<?php

namespace App\Service\Issue;

use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Repository\IssueRepository;
use App\Entity\Issue;
use App\Repository\SubscriberRepository;
use App\Service\NewsletterList\NewsletterListService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class SendService
{
    public function __construct(
        private SubscriberRepository $subscriberRepository,
    )
    {
    }

    /**
     * @return ArrayCollection<int, Subscriber>
     */
    public function getSendableSubscribers(Issue $issue): ArrayCollection
    {
        $project = $issue->getProject();

        /** @var list<Subscriber> $subscribers */
        $subscribers = $this->subscriberRepository
            ->createQueryBuilder('s')
            ->where('s.project = :project')
            ->andWhere('s.status = :status')
            ->andWhere(function ($query) use ($issue) {
                $listIds = $issue->getListIds();
                foreach ($listIds as $listId) {
                    $query
                        ->orWhere('s.list_ids ? :listId')
                        ->setParameter('listId', $listId);
                }
            })
            ->setParameter('project', $project)
            ->setParameter('status', SubscriberStatus::SUBSCRIBED->value)
            ->getQuery()
            ->getResult();
        dd($subscribers);
        return new ArrayCollection($subscribers);
    }

    public function getUnsubscribedUrl()
}
