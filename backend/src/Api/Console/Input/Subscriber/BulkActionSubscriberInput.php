<?php

namespace App\Api\Console\Input\Subscriber;

use Symfony\Component\Validator\Constraints as Assert;

class BulkActionSubscriberInput
{
    /**
     * @var int[] $subscribers_ids
     */
    #[Assert\NotBlank]
    public array $subscribers_ids;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['delete', 'metadata_update', 'status_change'], message: 'Invalid action.')]
    public string $action;

    public ?string $status = null;

    /**
     * @var array<string, string>
     */
    public ?array $metadata = null;
}
