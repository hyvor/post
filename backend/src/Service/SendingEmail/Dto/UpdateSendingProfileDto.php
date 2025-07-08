<?php

namespace App\Service\SendingEmail\Dto;

use App\Entity\Domain;
use App\Util\OptionalPropertyTrait;

class UpdateSendingProfileDto
{
    use OptionalPropertyTrait;

    public string $fromEmail;
    public ?string $fromName = null;
    public ?string $replyToEmail = null;
    public ?string $brandName = null;
    public ?string $brandLogo = null;
    public Domain $customDomain;
    public bool $isDefault;
}
