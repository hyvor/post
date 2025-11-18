<?php

namespace App\Api\Public\Object\Archive;

use App\Entity\Newsletter;

class NewsletterObject
{

    public string $subdomain;
    public string $name;

    public function __construct(Newsletter $newsletter)
    {
        $this->subdomain = $newsletter->getSubdomain();
        $this->name = $newsletter->getName();
    }
}
