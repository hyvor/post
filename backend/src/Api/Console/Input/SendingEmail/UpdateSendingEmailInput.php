<?php

namespace App\Api\Console\Input\SendingEmail;

use App\Util\OptionalPropertyTrait;
use Symfony\Component\Validator\Constraints as Assert;


class UpdateSendingEmailInput
{
    use OptionalPropertyTrait;

    #[Assert\Email]
    public string $email;
}
