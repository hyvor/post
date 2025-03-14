<?php

namespace App\Service\Template;

use Twig\Environment;

class TemplateRenderer
{

    public function __construct(
        private Environment $twig
    )
    {
    }


    public function render(): string
    {

        $template = $this->twig->createTemplate('Hello {{ name }}!');
        return $template->render(['name' => 'Supun']);

    }

}