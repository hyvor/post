<?php

namespace App\Service\Subscriber\ListRemoval;

use App\Entity\SubscriberListRemoval;
use App\Service\Subscriber\Event\SubscriberUpdatingEvent;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class ListRemovalListener
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    #[AsEventListener()]
    public function onSubscriberUpdating(SubscriberUpdatingEvent $event): void
    {
        $this->recordRemoving($event);
        $this->deleteAdding($event);
    }

    private function recordRemoving(SubscriberUpdatingEvent $event): void
    {
        $oldListIds = $event->getSubscriberOld()->getLists()->map(fn($list) => $list->getId())->toArray();
        $newListIds = $event->getSubscriber()->getLists()->map(fn($list) => $list->getId())->toArray();

        $removedListIds = array_diff($oldListIds, $newListIds);

        foreach ($removedListIds as $removedListId) {
            // PGSQL query with ON CONFLICT to update subscriber_list_removals

            $query = <<<SQL
                INSERT INTO subscriber_list_removals (list_id, subscriber_id, reason, created_at)
                VALUES (:list_id, :subscriber_id, :reason, :created_at)
                ON CONFLICT (list_id, subscriber_id) DO UPDATE
                SET reason = EXCLUDED.reason, created_at = EXCLUDED.created_at
                SQL;

            $params = [
                'list_id' => $removedListId,
                'subscriber_id' => $event->getSubscriber()->getId(),
                'reason' => $event->getListRemovalReason()->value,
                'created_at' => $this->now()->format('Y-m-d H:i:s'),
            ];

            $this->em->getConnection()->executeQuery($query, $params);
        }
    }

    private function deleteAdding(SubscriberUpdatingEvent $event): void
    {
        $oldListIds = $event->getSubscriberOld()->getLists()->map(fn($list) => $list->getId())->toArray();
        $newListIds = $event->getSubscriber()->getLists()->map(fn($list) => $list->getId())->toArray();

        $addedListIds = array_diff($newListIds, $oldListIds);

        if (count($addedListIds) === 0) {
            return;
        }

        $this->em->getConnection()->executeQuery(
            "DELETE FROM subscriber_list_removals
            WHERE subscriber_id = :subscriber_id
            AND list_id IN (:added_list_ids)",
            [
                'subscriber_id' => $event->getSubscriber()->getId(),
                'added_list_ids' => $addedListIds,
            ],
            [
                'subscriber_id' => \Doctrine\DBAL\ParameterType::INTEGER,
                'added_list_ids' => \Doctrine\DBAL\ArrayParameterType::INTEGER,
            ],
        );
    }

}
