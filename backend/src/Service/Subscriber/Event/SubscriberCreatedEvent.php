<?php

namespace App\Service\Subscriber\Event;

use App\Entity\Subscriber;
use Symfony\Contracts\EventDispatcher\Event;

class SubscriberCreatedEvent extends Event
{
    public const NAME = 'subscriber.created';

    public function __construct(
        private Subscriber $subscriber
    )
    {}

    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }
}
