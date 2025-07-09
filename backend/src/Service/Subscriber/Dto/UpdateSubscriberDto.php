<?php

namespace App\Service\Subscriber\Dto;

use App\Entity\NewsletterList;
use App\Entity\Type\SubscriberStatus;
use App\Util\OptionalPropertyTrait;

class UpdateSubscriberDto
{

    use OptionalPropertyTrait;

    public string $email;

    /**
     * @var iterable<NewsletterList>
     */
    public iterable $lists;

    public SubscriberStatus $status;

    public \DateTimeImmutable $unsubscribedAt;

    public ?string $unsubscribedReason;

    /**
     * @var array<string, string>
     */
    public array $metadata;

}
