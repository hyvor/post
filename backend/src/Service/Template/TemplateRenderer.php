<?php

namespace App\Service\Template;

use App\Service\Content\ContentService;
use App\Entity\Issue;
use App\Entity\Project;
use App\Service\Project\ProjectDefaults;
use Twig\Environment;

class TemplateRenderer
{

    const DEFAULT_CONTENT = <<<JSON
{
    "type": "doc"
}
JSON;


    public function __construct(
        private Environment $twig,
        private ContentService $contentService,
        private TemplateService $templateService,
    ) {
    }

    public function renderFromIssue(Project $project, Issue $issue): string
    {
        $meta = $project->getMeta();

        $variables = new TemplateVariables(
            lang: 'en',
            subject: (string)$issue->getSubject(),
            content: $this->contentService->htmlFromJson($issue->getContent() ?? self::DEFAULT_CONTENT),

            logo: $meta->template_logo ?? '',
            logo_alt: $meta->template_logo_alt ?? '',
            brand: $meta->brand ?? '',
            brand_url: $meta->brand_url ?? '',

            address: $meta->address ?? '',
            unsubscribe_url: 'https://example.com/unsubscribe',
            unsubscribe_text: $meta->unsubscribe_text ?? '',

            color_accent: $meta->template_color_accent ?? ProjectDefaults::TEMPLATE_COLOR_ACCENT,
            color_background: $meta->color_background ?? ProjectDefaults::TEMPLATE_COLOR_BACKGROUND,
            color_box_background: $meta->color_box_background ?? ProjectDefaults::TEMPLATE_COLOR_BOX_BACKGROUND,

            font_family: $meta->font_family ?? ProjectDefaults::TEMPLATE_FONT_FAMILY,
            font_size: $meta->font_size ?? ProjectDefaults::TEMPLATE_FONT_SIZE,
            font_weight: $meta->font_weight ?? ProjectDefaults::TEMPLATE_FONT_WEIGHT,
            font_weight_heading: $meta->font_weight ?? ProjectDefaults::TEMPLATE_FONT_WEIGHT_HEADING,
            font_color_on_background: $meta->font_color_on_background ?? ProjectDefaults::TEMPLATE_FONT_COLOR_ON_BACKGROUND,
            font_color_on_box: $meta->font_color_on_box ?? ProjectDefaults::TEMPLATE_FONT_COLOR_ON_BOX,
            font_line_height: $meta->font_line_height ?? ProjectDefaults::TEMPLATE_FONT_LINE_HEIGHT,

            box_radius: $meta->box_radius ?? ProjectDefaults::TEMPLATE_BOX_RADIUS,
            box_shadow: $meta->box_shadow ?? ProjectDefaults::TEMPLATE_BOX_SHADOW,
            box_border: $meta->box_border ?? ProjectDefaults::TEMPLATE_BOX_BORDER,
        );

        $template = $this->templateService->getTemplateStringFromProject($issue->getProject());
        return $this->render($template, $variables);
    }

    public function render(string $template, TemplateVariables $variables): string
    {
        $template = $this->twig->createTemplate($template);
        return $template->render((array)$variables);
    }

    public function renderDefaultTemplate(TemplateVariables $variables): string
    {
        $defaultTemplate = $this->templateService->readDefaultTemplate();
        $template = $this->twig->createTemplate($defaultTemplate);
        return $template->render((array)$variables);
    }
}
