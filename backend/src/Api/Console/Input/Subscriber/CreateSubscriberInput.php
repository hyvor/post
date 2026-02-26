<?php

namespace App\Api\Console\Input\Subscriber;

use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Util\OptionalPropertyTrait;
use Symfony\Component\Validator\Constraints as Assert;

class CreateSubscriberInput
{

    use OptionalPropertyTrait;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email;

    public SubscriberStatus $status = SubscriberStatus::PENDING;

    public ?SubscriberSource $source;

    #[Assert\Ip(version: Assert\Ip::ALL_ONLY_PUBLIC)]
    public ?string $subscribe_ip;

    public ?int $subscribed_at;

    public ?int $unsubscribed_at;

    public CreateSubscriberIfExists $if_exists = CreateSubscriberIfExists::ERROR;

}
