<?php

namespace App\Service\EmailTemplate\Dto;

use App\Util\OptionalPropertyTrait;

class UpdateTemplateDto
{
    use OptionalPropertyTrait;

    public string $template;
}
