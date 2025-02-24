<?php

namespace App\Api\Console\Input\Subscriber;
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

}
