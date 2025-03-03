<?php

namespace App\Api\Console\Input\Subscriber;

use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use Symfony\Component\Validator\Constraints as Assert;

class CreateSubscriberInput
{

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email;

    /**
     * @var int[] $list_ids
     */
    #[Assert\NotBlank]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Type('int'),
    ])]
    public array $list_ids;

    public ?SubscriberStatus $status = null;

    public ?SubscriberSource $source = null;

    #[Assert\Ip(version: Assert\Ip::ALL_ONLY_PUBLIC)]
    public ?string $subscribe_ip = null;

    public ?int $subscribed_at = null;

    public ?int $unsubscribed_at = null;
}
