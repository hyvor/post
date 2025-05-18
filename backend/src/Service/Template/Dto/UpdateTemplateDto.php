<?php

namespace App\Service\Template\Dto;

use App\Util\OptionalPropertyTrait;

class UpdateTemplateDto
{
    use OptionalPropertyTrait;

    public string $template;
}
