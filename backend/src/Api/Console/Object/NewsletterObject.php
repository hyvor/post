<?php

namespace App\Api\Console\Object;

use App\Entity\Meta\NewsletterMeta;
use App\Entity\Newsletter;

class NewsletterObject extends NewsletterMeta
{

    public int $id;
    public string $subdomain;
    public int $created_at; // unix timestamp
    public string $name;
    public ?string $language_code;
    public bool $is_rtl;

    public function __construct(Newsletter $newsletter)
    {
        $this->id = $newsletter->getId();
        $this->subdomain = $newsletter->getSubdomain();
        $this->created_at = $newsletter->getCreatedAt()->getTimestamp();
        $this->name = $newsletter->getName();
        $this->language_code = $newsletter->getLanguageCode();
        $this->is_rtl = $newsletter->isRtl();

        $meta = $newsletter->getMeta();
        foreach (get_object_vars($meta) as $property => $value) {
            $this->$property = $value;
        }
    }

}
