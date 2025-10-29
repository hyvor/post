<?php

namespace App\Api\Sudo\Object;

use App\Entity\Type\SubscriberStatus;
use App\Service\Import\Dto\ImportingSubscriberDto;

class ImportingSubscriberObject
{
    public string $email;
    /** @var string[] */
    public array $lists;
    public SubscriberStatus $status;
    public ?int $subscribed_at;
    public ?string $subscribe_ip;
    /** @var array<string, string> | null */
    public ?array $metadata;

    public function __construct(ImportingSubscriberDto $importingSubscriber)
    {
        $this->email = $importingSubscriber->email;
        $this->lists = $importingSubscriber->lists;
        $this->status = $importingSubscriber->status;
        $this->subscribed_at = $importingSubscriber->subscribedAt?->getTimestamp();
        $this->subscribe_ip = $importingSubscriber->subscribeIp;
        $this->metadata = $importingSubscriber->metadata;
    }
}