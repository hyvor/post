<?php

namespace App\Service\SendingEmail\Dto;

use App\Entity\Domain;
use App\Util\OptionalPropertyTrait;

class UpdateSendingProfileDto
{
    use OptionalPropertyTrait;

    public string $email;

    public Domain $customDomain;

    public bool $isDefault;
}
