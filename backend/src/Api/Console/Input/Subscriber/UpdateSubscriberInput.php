<?php

namespace App\Api\Console\Input\Subscriber;

use App\Entity\Type\SubscriberStatus;
use App\Util\OptionalPropertyTrait;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateSubscriberInput
{

    use OptionalPropertyTrait;

    #[Assert\Email]
    public string $email;

    public SubscriberStatus $status;

    /**
     * @var array<string, string>
     */
    public array $metadata;
}
