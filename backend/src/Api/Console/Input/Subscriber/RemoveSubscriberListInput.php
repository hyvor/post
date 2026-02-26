<?php

namespace App\Api\Console\Input\Subscriber;

class RemoveSubscriberListInput
{
    public ?int $id = null;
    public ?string $name = null;
    public RemoveSubscriberListReason $reason = RemoveSubscriberListReason::OTHER;
}
