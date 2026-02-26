<?php

namespace App\Api\Console\Input\Subscriber;

class AddSubscriberListInput
{
    public ?int $id = null;
    public ?string $name = null;
    public SubscriberListIfUnsubscribed $if_unsubscribed = SubscriberListIfUnsubscribed::ERROR;
}
