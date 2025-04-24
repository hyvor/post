<?php

namespace App\Api\Console\Object;

use App\Entity\Template;

class TemplateObject
{
    public string $template;

    public function __construct(Template $t)
    {
        $this->template = $t->getTemplate();
    }
}
