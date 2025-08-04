<?php

namespace App\Api\Public\Input\Subscriber;

use Symfony\Component\Validator\Constraints as Assert;

class ResubscribeInput
{

    /**
     * @var int[]
     */
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Type('int'),
    ])]
    public array $list_ids;

    #[Assert\NotBlank]
    public string $token;
}
