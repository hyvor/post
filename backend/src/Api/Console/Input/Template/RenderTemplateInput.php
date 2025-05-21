<?php

namespace App\Api\Console\Input\Template;

use Symfony\Component\Validator\Constraints as Assert;

class RenderTemplateInput
{
    #[Assert\NotBlank]
    public string $template;
}
