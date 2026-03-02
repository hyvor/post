<?php

namespace App\Api\Console\Input\UserInvite;

use App\Entity\Type\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserInput
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    public int $user_id;

//    #[Assert\NotBlank]
//    public UserRole $role;
}
