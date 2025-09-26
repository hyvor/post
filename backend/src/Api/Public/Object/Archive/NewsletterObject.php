<?php

namespace App\Api\Public\Object\Archive;

use App\Entity\Newsletter;

class NewsletterObject
{

    public string $uuid;
    public string $subdomain;
    public string $name;
    public ?string $logo;

    public function __construct(Newsletter $newsletter)
    {
        $this->uuid = $newsletter->getUuid();
        $this->subdomain = $newsletter->getSubdomain();
        $this->name = $newsletter->getName();

        $meta = $newsletter->getMeta();
        $this->logo = $meta->logo;
    }

}
