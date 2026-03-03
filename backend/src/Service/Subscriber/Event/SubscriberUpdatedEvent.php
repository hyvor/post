<?php

namespace App\Service\Subscriber\Event;

use App\Entity\Subscriber;

readonly class SubscriberUpdatedEvent
{

    public function __construct(
        private Subscriber $subscriberOld,
        private Subscriber $subscriber,
    ) {}

    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }

    public function getSubscriberOld(): Subscriber
    {
        return $this->subscriberOld;
    }

}
