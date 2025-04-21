<?php

namespace App\Api\Public\Input;

use Symfony\Component\Validator\Constraints as Assert;

class TemplateRenderWithInput
{

    #[Assert\NotBlank]
    public string $template;

    #[Assert\NotBlank]
    public string $variables;

}
