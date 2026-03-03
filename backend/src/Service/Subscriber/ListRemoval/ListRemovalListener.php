<?php

namespace App\Service\Subscriber\ListRemoval;

use App\Service\Subscriber\Event\SubscriberUpdatingEvent;
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
        $this->skipRemoved($event);
        $this->recordRemoving($event);
    }

    private function skipRemoved(SubscriberUpdatingEvent $event): void
    {
        //
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

}
