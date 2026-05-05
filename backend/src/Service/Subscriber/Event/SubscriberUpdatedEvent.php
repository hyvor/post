<?php

namespace App\Service\Subscriber\Event;

use App\Entity\Subscriber;

readonly class SubscriberUpdatedEvent
{

    public function __construct(
        private Subscriber $subscriberOld,
        private Subscriber $subscriber,
        // whether to send confirmation email if status changed to pending
        private bool $sendConfirmationEmail,
    ) {}

    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }

    public function getSubscriberOld(): Subscriber
    {
        return $this->subscriberOld;
    }

    public function shouldSendConfirmationEmail(): bool
    {
        return $this->sendConfirmationEmail;
    }

}
