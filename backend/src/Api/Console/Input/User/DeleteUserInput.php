<?php

namespace App\Api\Console\Input\User;

use Symfony\Component\Validator\Constraints as Assert;

class DeleteUserInput
{
    #[Assert\Positive]
    public ?int $user_id = null;

    #[Assert\Positive]
    public ?int $id = null;
}
