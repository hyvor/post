<?php

namespace App\Api\Console\Object;

use App\Entity\Send;
use App\Entity\Subscriber;
use App\Entity\Type\SendStatus;

class SendObject
{
    public int $id;
    public int $created_at;

    public ?Subscriber $subscriber;
    public string $email;

    public SendStatus $status;

    public ?int $sent_at;
    public ?int $failed_at;
    public ?int $delivered_at;

    public ?int $first_opened_at;
    public ?int $last_opened_at;


    public ?int $first_clicked_at;
    public ?int $last_clicked_at;

    public ?int $unsubscribed_at;
    public ?int $bounced_at;
    public bool $hard_bounce;
    public ?int $complained_at;

    public int $open_count;
    public int $click_count;

    public function __construct(Send $send)
    {
        $this->id = $send->getId();
        $this->created_at = $send->getCreatedAt()->getTimestamp();

        $this->subscriber = $send->getSubscriber();
        $this->email = $send->getEmail();

        $this->status = $send->getStatus();

        $this->sent_at = $send->getSentAt()?->getTimestamp();
        $this->failed_at = $send->getFailedAt()?->getTimestamp();
        $this->delivered_at = $send->getDeliveredAt()?->getTimestamp();

        $this->first_opened_at = $send->getFirstOpenAt()?->getTimestamp();
        $this->last_opened_at = $send->getLastOpenedAt()?->getTimestamp();

        $this->first_clicked_at = $send->getFirstClickedAt()?->getTimestamp();
        $this->last_clicked_at = $send->getLastClickedAt()?->getTimestamp();

        $this->unsubscribed_at = $send->getUnsubscribeAt()?->getTimestamp();

        $this->bounced_at = $send->getBouncedAt()?->getTimestamp();
        $this->hard_bounce = $send->isHardBounce();

        $this->complained_at = $send->getComplainedAt()?->getTimestamp();

        $this->open_count = $send->getOpenCount();
        $this->click_count = $send->getClickCount();

    }
}
