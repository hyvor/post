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

    /**
     * @var int[] $list_ids
     */
    #[Assert\Count(min: 1, minMessage: "There should be at least one list.")]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Type('int'),
    ])]
    public array $list_ids;

    public SubscriberStatus $status;

    /**
     * @var array<string, string>
     */
    public array $metadata;
}
