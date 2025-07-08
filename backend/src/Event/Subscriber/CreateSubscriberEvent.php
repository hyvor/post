<?php

namespace App\Event\Subscriber;

use App\Entity\Subscriber;
use Symfony\Contracts\EventDispatcher\Event;

class CreateSubscriberEvent extends Event
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
