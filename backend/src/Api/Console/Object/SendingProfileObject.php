<?php

namespace App\Api\Console\Object;

use App\Entity\SendingProfile;
use App\Service\SendingProfile\SendingProfileService;

class SendingProfileObject
{
    public int $id;
    public int $created_at;
    public string $from_email;
    public ?string $from_name;
    public ?string $reply_to_email;
    public ?string $brand_name;
    public ?string $brand_logo;
    public ?string $brand_url;
    public bool $is_default;
    public bool $is_system;

    public function __construct(SendingProfile $sendingProfile)
    {
        $this->id = $sendingProfile->getId();
        $this->created_at = $sendingProfile->getCreatedAt()->getTimestamp();
        $this->is_default = $sendingProfile->getIsDefault();
        $this->is_system = $sendingProfile->getIsSystem();
        $this->from_email = $sendingProfile->getFromEmail();
        $this->from_name = $sendingProfile->getFromName();
        $this->reply_to_email = $sendingProfile->getReplyToEmail();
        $this->brand_name = $sendingProfile->getBrandName();
        $this->brand_logo = $sendingProfile->getBrandLogo();
        $this->brand_url = $sendingProfile->getBrandUrl();
    }
}
