<?php

namespace App\Api\Console\Object;

use App\Entity\Subscriber;

class SubscriberObject
{

    public int $id;
    public string $email;
    public string $source;
    public string $status;
    /**
     * @var array<int>
     */
    public array $list_ids;
    public ?string $subscribe_ip;
    public ?int $subscribed_at;
    public ?int $unsubscribed_at;

    public function __construct(Subscriber $subscriber)
    {
        $this->id = $subscriber->getId();
        $this->email = $subscriber->getEmail();
        $this->source = $subscriber->getSource()->value;
        $this->status = $subscriber->getStatus()->value;
        $this->list_ids = array_values($subscriber->getLists()->map(fn($list) => $list->getId())->toArray());
        $this->subscribe_ip = $subscriber->getSubscribeIp();
        $this->subscribed_at = $subscriber->getSubscribedAt()?->getTimestamp();
        $this->unsubscribed_at = $subscriber->getUnsubscribedAt()?->getTimestamp();
    }

}
