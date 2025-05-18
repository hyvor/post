<?php

namespace App\Api\Console\Input\UserInvite;

use App\Entity\Type\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

class InviteUserInput
{

    public ?string $username = null;

    #[Assert\Email]
    public ?string $email = null;

//    #[Assert\NotBlank]
//    public UserRole $role;
}
