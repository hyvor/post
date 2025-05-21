<?php

namespace App\Api\Console\Input\SendingEmail;

use Symfony\Component\Validator\Constraints as Assert;

class CreateSendingEmailInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    public string $email;
}
