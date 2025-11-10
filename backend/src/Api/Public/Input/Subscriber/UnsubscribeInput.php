<?php

namespace App\Api\Public\Input\Subscriber;

use Symfony\Component\Validator\Constraints as Assert;

class UnsubscribeInput
{
    #[Assert\NotBlank]
    public string $token;
}
