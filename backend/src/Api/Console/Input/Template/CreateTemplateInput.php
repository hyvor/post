<?php

namespace App\Api\Console\Input\Template;

use App\Util\OptionalPropertyTrait;
use Symfony\Component\Validator\Constraints as Assert;

class CreateTemplateInput
{
    use OptionalPropertyTrait;

    public ?string $template;
}
