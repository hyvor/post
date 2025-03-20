<?php

namespace App\Api\Console\Input\Issue;

use Symfony\Component\Validator\Constraints as Assert;
class SendTestInput
{

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email;
}
