<?php

namespace App\Service\Newsletter\Dto;

use App\Util\OptionalPropertyTrait;

class UpdateNewsletterDto
{
    use OptionalPropertyTrait;

    public string $name;
    public string $subdomain;

    /**
     * @var string[]|null
     */
    public ?array $allowed_domains;
}
