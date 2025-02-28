<?php

namespace App\Api\Console\Input\Subscriber;
use App\Enum\SubscriberSource;
use App\Enum\SubscriberStatus;
use Symfony\Component\Validator\Constraints as Assert;
class CreateSubscriberInput
{

    #[Assert\NotBlank]
    #[Assert\Email]
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

    #[Assert\Choice(callback: [SubscriberStatus::class, 'cases'])]
    public ?string $status = null;

    #[Assert\Choice(callback: [SubscriberSource::class, 'cases'])]
    public ?string $source = null;

    public ?string $subscribe_ip = null;

    public ?\DateTimeImmutable $subscribed_at = null;

    public ?\DateTimeImmutable $unsubscribed_at = null;
}
