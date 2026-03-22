<?php

namespace App\Service\Subscriber\ConfirmationMail;

use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Service\Subscriber\Event\SubscriberCreatedEvent;
use App\Service\Subscriber\Event\SubscriberUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

class ConfirmationMailListener
{

    public function __construct(private MessageBusInterface $bus) {}

    #[AsEventListener]
    public function onSubscriberCreate(SubscriberCreatedEvent $event): void
    {
        if (
            $event->shouldSendConfirmationEmail() &&
            $event->getSubscriber()->getStatus() === SubscriberStatus::PENDING
        ) {
            $this->send($event->getSubscriber());
        }
    }

    #[AsEventListener]
    public function onSubscriberUpdate(SubscriberUpdatedEvent $event): void
    {
        if (
            $event->getSubscriberOld()->getStatus() !== SubscriberStatus::PENDING &&
            $event->getSubscriber()->getStatus() === SubscriberStatus::PENDING &&
            $event->shouldSendConfirmationEmail()
        ) {
            $this->send($event->getSubscriber());
        }
    }

    private function send(Subscriber $subscriber): void
    {
        $this->bus->dispatch(new SendConfirmationMailMessage($subscriber->getId()));
    }


}
