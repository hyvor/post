<?php

namespace App\Api\Console\Object;

use App\Entity\Subscriber;

class SubscriberObject
{

    public int $id;
    public string $email;
    public string $source;
    public string $status;

    public function __construct(Subscriber $subscriber)
    {
        $this->id = $subscriber->getId();
        $this->email = $subscriber->getEmail();
        $this->source = $subscriber->getSource()->name;
        $this->status = $subscriber->getStatus()->name;
    }

}
