<?php

namespace App\Api\Sudo\Object;

use App\Entity\Newsletter;

class NewsletterObject
{
    public int $id;
    public int $created_at;
    public string $subdomain;
    public string $name;
    public int $user_id;
    public ?int $organization_id;
    public ?string $language_code;
    public bool $is_rtl;

    public function __construct(Newsletter $newsletter)
    {
        $this->id = $newsletter->getId();
        $this->created_at = $newsletter->getCreatedAt()->getTimestamp();
        $this->subdomain = $newsletter->getSubdomain();
        $this->name = $newsletter->getName();
        $this->user_id = $newsletter->getUserId();
        $this->organization_id = $newsletter->getOrganizationId();
        $this->language_code = $newsletter->getLanguageCode();
        $this->is_rtl = $newsletter->isRtl();
    }
}
