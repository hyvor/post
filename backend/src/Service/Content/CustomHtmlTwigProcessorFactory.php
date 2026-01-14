<?php

namespace App\Service\Content;

use App\Service\Template\TemplateVariables;
use Twig\Environment;

class CustomHtmlTwigProcessorFactory
{

    public function __construct(private Environment $twig)
    {
    }

    public function create(?TemplateVariables $variables): CustomHtmlTwigProcessor
    {
        return new CustomHtmlTwigProcessor(
            $this->twig,
            $variables ? (array)$variables : []
        );
    }

}