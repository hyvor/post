<?php

namespace App\Service\Newsletter\Dto;

use App\Util\OptionalPropertyTrait;

class UpdateNewsletterDto
{
    use OptionalPropertyTrait;

    public string $name;
    public string $subdomain;
    public ?string $language_code;
    public bool $is_rtl;
}
