<?php

namespace App\Api\Public\Object\NewsletterPage;

use App\Entity\Newsletter;

class NewsletterObject
{

    public string $uuid;
    public string $slug;
    public string $name;
    public ?string $logo;

    public function __construct(Newsletter $newsletter)
    {
        $this->uuid = $newsletter->getUuid();
        $this->slug = $newsletter->getSlug();
        $this->name = $newsletter->getName();

        $meta = $newsletter->getMeta();
        $this->logo = $meta->logo;
    }

}