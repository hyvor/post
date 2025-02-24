<?php

namespace App\Api\Console\Object;

use App\Entity\Subscriber;

class SubscriberObject
{

    public int $id;
    public string $email;

    public function __construct(Subscriber $subscriber)
    {
        $this->id = $subscriber->getId();
        $this->email = $subscriber->getEmail();
    }

}