<?php

namespace App\Api\Public\Object\Form;


use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;

class FormSubscriberObject
{

    public int $id;
    /** @var int[] */
    public array $list_ids;
    public string $email;
    public SubscriberStatus $status;
    public ?int $subscribed_at;
    public ?int $unsubscribed_at;

    public function __construct(Subscriber $subscriber)
    {
        $this->id = $subscriber->getId();
        $this->list_ids = array_values($subscriber->getLists()->map(fn($list) => $list->getId())->toArray());
        $this->email = $subscriber->getEmail();
        $this->status = $subscriber->getStatus();
        $this->subscribed_at = $subscriber->getSubscribedAt()?->getTimestamp();
        $this->unsubscribed_at = $subscriber->getUnsubscribedAt()?->getTimestamp();
    }

}