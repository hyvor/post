<?php

namespace App\Entity\Factory;

use App\Entity\Subscriber;

/**
 * @extends FactoryAbstract<Subscriber>
 */
class SubscriberFactory extends FactoryAbstract
{

    public function define() : Subscriber
    {
        $subscriber = new Subscriber();
        $subscriber->setCreatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeThisYear()));
        $subscriber->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->fake->dateTimeThisYear()));
        $subscriber->setEmail($this->fake->email());
        return $subscriber;
    }

}
