<?php

namespace App\Api\Public\Input;

use Symfony\Component\Validator\Constraints as Assert;

class TemplateRenderWithInput
{

    public string $template;
    #[Assert\Json]
    public string $variables;

}
