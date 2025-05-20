<?php

namespace App\Service\SendingEmail\Dto;

use App\Entity\Domain;
use App\Util\OptionalPropertyTrait;

class UpdateSendingAddress
{
    use OptionalPropertyTrait;

    public string $email;

    public Domain $customDomain;
}
