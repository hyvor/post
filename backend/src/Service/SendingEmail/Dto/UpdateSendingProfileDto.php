<?php

namespace App\Service\SendingEmail\Dto;

use App\Entity\Domain;
use App\Util\OptionalPropertyTrait;

class UpdateSendingProfileDto
{
    use OptionalPropertyTrait;

    public string $fromEmail;
    public ?string $fromName;
    public ?string $replyToEmail;
    public ?string $brandName;
    public ?string $brandLogo;
    public Domain $customDomain;
    public bool $isDefault;
}
