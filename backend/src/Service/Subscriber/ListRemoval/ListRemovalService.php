<?php

namespace App\Service\Subscriber\ListRemoval;

use App\Entity\Subscriber;
use App\Entity\SubscriberListRemoval;
use App\Entity\Type\ListRemovalReason;
use Doctrine\ORM\EntityManagerInterface;

class ListRemovalService
{

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    /**
     * @param int[] $listIds
     * @param ListRemovalReason[] $reasons
     * @return SubscriberListRemoval[]
     */
    public function getRemovals(
        Subscriber $subscriber,
        array $listIds,
        array $reasons,
    ): array {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('r')
            ->from(SubscriberListRemoval::class, 'r')
            ->where('r.subscriber = :subscriber')
            ->andWhere($qb->expr()->in('r.list', ':listIds'))
            ->andWhere($qb->expr()->in('r.reason', ':reasons'))
            ->setParameter('subscriber', $subscriber)
            ->setParameter('listIds', $listIds)
            ->setParameter('reasons', $reasons);

        /** @var SubscriberListRemoval[] */
        return $qb->getQuery()->getResult();
    }

}
