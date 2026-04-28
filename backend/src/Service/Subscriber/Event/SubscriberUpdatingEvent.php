<?php

namespace App\Service\Subscriber\Event;

use App\Entity\Subscriber;
use App\Entity\Type\ListRemovalReason;

readonly class SubscriberUpdatingEvent
{

    public function __construct(
        private Subscriber $subscriberOld,
        private Subscriber $subscriber,
        private ListRemovalReason $listRemovalReason,
    ) {}

    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }

    public function getSubscriberOld(): Subscriber
    {
        return $this->subscriberOld;
    }

    public function getListRemovalReason(): ListRemovalReason
    {
        return $this->listRemovalReason;
    }

}
