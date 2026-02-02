<?php

namespace App\Api\Console\Input\UserInvite;

use App\Entity\Type\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

class InviteUserInput
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    public int $userId;

//    #[Assert\NotBlank]
//    public UserRole $role;
}
