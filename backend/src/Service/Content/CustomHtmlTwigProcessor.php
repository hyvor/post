<?php

namespace App\Service\Content;

use App\Service\Content\Nodes\CustomHtml;
use App\Service\Template\TemplateVariables;
use Twig\Environment;

class CustomHtmlTwigProcessor
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    /**
     * Process Twig syntax within CustomHtml blocks only.
     *
     * @param string $html The rendered HTML content containing CustomHtml markers
     * @param TemplateVariables $variables The template variables to make available
     * @return string The HTML with CustomHtml blocks processed through Twig
     */
    public function process(string $html, TemplateVariables $variables): string
    {
        if (!str_contains($html, CustomHtml::MARKER_START)) {
            return $html;
        }

        $pattern = '/' . preg_quote(CustomHtml::MARKER_START, '/') .
                   '(.*?)' .
                   preg_quote(CustomHtml::MARKER_END, '/') . '/s';

        return preg_replace_callback($pattern, function ($matches) use ($variables) {
            return $this->renderTwig($matches[1], $variables);
        }, $html) ?? $html;
    }

    /**
     * Render a single CustomHtml block through Twig.
     */
    private function renderTwig(string $content, TemplateVariables $variables): string
    {
        try {
            $template = $this->twig->createTemplate($content);
            return $template->render((array) $variables);
        } catch (\Throwable) {
            return $content;
        }
    }
}
