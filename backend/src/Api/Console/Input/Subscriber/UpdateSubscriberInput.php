<?php

namespace App\Api\Console\Input\Subscriber;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateSubscriberInput
{
    #[Assert\Email]
    public string $email;

    /**
     * @var int[] $list_ids
     */
    #[Assert\Count(min: 1, minMessage: "There should be at least one list")]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Type('int'),
    ])]
    public array $list_ids;

    #[Assert\Choice(choices: ['subscribed', 'unsubscribed', 'pending'])]
    public string $status;
}
