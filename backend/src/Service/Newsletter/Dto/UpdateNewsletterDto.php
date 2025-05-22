<?php

namespace App\Service\Newsletter\Dto;

use App\Util\OptionalPropertyTrait;

class UpdateNewsletterDto
{
    use OptionalPropertyTrait;

    public string $name;

    public ?string $defaultEmailUsername;
}
