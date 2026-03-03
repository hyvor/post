<?php

namespace App\Service\Subscriber\Dto;

use App\Entity\NewsletterList;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Util\OptionalPropertyTrait;

class UpdateSubscriberDto
{

    use OptionalPropertyTrait;

    public SubscriberStatus $status;
    public SubscriberSource $source;
    public ?string $subscribeIp;

    public ?\DateTimeImmutable $subscribedAt;

    public ?\DateTimeImmutable $optInAt;

    public ?string $unsubscribedReason;

    /** @var NewsletterList[] */
    public array $lists;

    /**
     * @var array<string, scalar>
     */
    public array $metadata;

}
