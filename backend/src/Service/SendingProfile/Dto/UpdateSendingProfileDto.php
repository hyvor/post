<?php

namespace App\Service\SendingProfile\Dto;

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
    public ?string $brandUrl;
    public Domain $customDomain;
    public bool $isDefault;
}
