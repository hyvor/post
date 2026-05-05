<?php

namespace App\Api\Console\Object;

use App\Entity\Subscriber;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;

class SubscriberObject
{

    public int $id;
    public string $email;
    public SubscriberSource $source;
    public SubscriberStatus $status;
    /**
     * @var array<int>
     */
    public array $list_ids;
    /**
     * @var array<string>
     */
    public array $lists;
    public ?string $subscribe_ip;
    public ?int $subscribed_at;

    /**
     * @var array<string, scalar>
     */
    public array $metadata;

    public function __construct(Subscriber $subscriber)
    {
        $this->id = $subscriber->getId();
        $this->email = $subscriber->getEmail();
        $this->source = $subscriber->getSource();
        $this->status = $subscriber->getStatus();
        $subscriberLists = $subscriber->getLists();
        $this->list_ids = array_values($subscriberLists->map(fn($list) => $list->getId())->toArray());
        $this->lists = array_values($subscriberLists->map(fn($list) => $list->getName())->toArray());
        $this->subscribe_ip = $subscriber->getSubscribeIp();
        $this->subscribed_at = $subscriber->getSubscribedAt()?->getTimestamp();
        $this->metadata = $subscriber->getMetadata();
    }
}
