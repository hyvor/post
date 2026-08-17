<?php

namespace App\Api\Console\Input\User;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserInput
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    public int $user_id;

    #[Assert\Choice(choices: ['throw', 'ignore'])]
    public string $on_duplicate = 'throw';
}
