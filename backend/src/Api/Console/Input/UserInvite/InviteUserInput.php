<?php

namespace App\Api\Console\Input\UserInvite;

use App\Entity\Type\UserRole;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class InviteUserInput
{

    public ?string $username = null;

    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    public UserRole $role;
}
