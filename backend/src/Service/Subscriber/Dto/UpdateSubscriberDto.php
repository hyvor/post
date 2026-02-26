<?php

namespace App\Service\Subscriber\Dto;

use App\Entity\NewsletterList;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Util\OptionalPropertyTrait;

class UpdateSubscriberDto
{

    use OptionalPropertyTrait;

    public string $email;

    public SubscriberStatus $status;
    public SubscriberSource $source;
    public ?string $subscribeIp;

    public ?\DateTimeImmutable $subscribedAt;

    public ?\DateTimeImmutable $optInAt;

    public ?\DateTimeImmutable $unsubscribedAt;

    public ?string $unsubscribedReason;

    /**
     * @var array<string, string>
     */
    public array $metadata;

}
