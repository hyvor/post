<?php

namespace App\Service\Subscriber\MessageHandler;

use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Service\Subscriber\Message\ClearPendingSubscribersMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ClearPendingSubscribersMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function __invoke(ClearPendingSubscribersMessage $message): void
    {
        $this->em->createQueryBuilder()
            ->delete(Subscriber::class, 's')
            ->where('s.status = :status')
            ->andWhere('s.created_at < :date')
            ->setParameter('status', SubscriberStatus::PENDING)
            ->setParameter('date', new \DateTimeImmutable('-48 hours'))
            ->getQuery()
            ->execute();

        $this->em->flush();
    }
}
