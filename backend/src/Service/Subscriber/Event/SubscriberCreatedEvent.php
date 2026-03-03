<?php

namespace App\Service\Subscriber\Event;

use App\Entity\Subscriber;

readonly class SubscriberCreatedEvent
{

    public function __construct(
        private Subscriber $subscriber,
        private bool $sendConfirmationEmail,
    ) {}

    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }

    public function shouldSendConfirmationEmail(): bool
    {
        return $this->sendConfirmationEmail;
    }

}
