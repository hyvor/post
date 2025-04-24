<?php

namespace App\Api\Console\Object;

use App\Entity\NewsletterList;

class ListObject
{

    public int $id;
    public int $created_at; // unix timestamp
    public string $name;
    public ?string $description;
    public int $subscribers_count;
    public int $subscribers_count_last_30d;

    public function __construct(NewsletterList $newsletterList)
    {
        $this->id = $newsletterList->getId();
        $this->created_at = $newsletterList->getCreatedAt()->getTimestamp();
        $this->name = $newsletterList->getName();
        $this->description = $newsletterList->getDescription();
        $this->subscribers_count = $newsletterList->getSubscribers()->count();
        $this->subscribers_count_last_30d = $newsletterList->getSubscribers()->filter(fn($subscriber) =>
            $subscriber->getCreatedAt()->getTimestamp() > strtotime('-30 days'))->count();
    }
}
