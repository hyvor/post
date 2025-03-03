<?php

namespace App\Service\Subscriber\Dto;

use App\Entity\NewsletterList;
use App\Enum\SubscriberStatus;
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

}