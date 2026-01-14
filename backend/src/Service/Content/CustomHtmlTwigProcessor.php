<?php

namespace App\Service\Content;

use App\Service\Template\TemplateVariables;
use Twig\Environment;

class CustomHtmlTwigProcessor
{

    public function __construct(
        private Environment $twig,
        /**
         * @var array<mixed>
         */
        private array $variables,
    ) {
    }

    public function render(string $content): string
    {
        try {
            $template = $this->twig->createTemplate($content);
            return $template->render($this->variables);
        } catch (\Twig\Error\Error $e) {
            return 'Unable to render twig: ' . $e->getMessage();
        } catch (\Throwable) {
            return $content;
        }
    }
}
