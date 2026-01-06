<?php

namespace App\Service\Content;

use App\Service\Template\TemplateVariables;
use Twig\Environment;

class CustomHtmlTwigProcessor
{
    private ?TemplateVariables $variables = null;

    public function __construct(
        private Environment $twig,
    ) {
    }

    /**
     * Create a new processor instance with the given variables.
     */
    public function with(TemplateVariables $variables): self
    {
        $new = new self($this->twig);
        $new->variables = $variables;
        return $new;
    }

    /**
     * Render Twig syntax in the given content.
     */
    public function render(string $content): string
    {
        if ($this->variables === null) {
            return $content;
        }

        try {
            $template = $this->twig->createTemplate($content);
            return $template->render((array) $this->variables);
        } catch (\Throwable) {
            return $content;
        }
    }
}
