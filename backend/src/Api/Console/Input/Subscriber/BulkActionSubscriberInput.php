<?php

namespace App\Api\Console\Input\Subscriber;

class BulkActionSubscriberInput
{
    /**
     * @var int[] $subscribers_ids
     */
    public array $subscribers_ids;
    public string $action;

    public ?string $status = null;

    /**
     * @var array<string, string>
     */
    public ?array $metadata = null;
}
