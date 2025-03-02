<?php

namespace App\Entity\Factory;

use App\Entity\Subscriber;
use App\Enum\SubscriberSource;
use App\Enum\SubscriberStatus;

/**
 * @extends FactoryAbstract<Subscriber>
 * @deprecated
 */
class SubscriberFactory extends FactoryAbstract
{

    public function define() : Subscriber
    {
        $subscriber = new Subscriber();
        $subscriber->setCreatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeThisYear()));
        $subscriber->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeThisYear()));
        $subscriber->setEmail($this->fake->email());
        $subscriber->setStatus(SubscriberStatus::SUBSCRIBED);
        $subscriber->setSource(SubscriberSource::CONSOLE);
        return $subscriber;
    }

}
