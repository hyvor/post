<?php

namespace App\Service\Template;

use App\Entity\Issue;
use App\Entity\Project;
use Twig\Environment;

class TemplateRenderer
{

    public function __construct(
        private Environment $twig
    )
    {
    }

    public function renderFromIssue(Project $project, Issue $issue): string
    {
        $meta = $project->getMeta();

        // TODO:
        $variables = new TemplateVariables(
            lang: 'en',
            subject: (string) $issue->getSubject(),
            content: (string) $issue->getHtml(),

            logo: $meta->template_logo ?? '',
            logo_alt: $meta->template_logo_alt ?? '',
            brand: $meta->brand ?? '',
            brand_url: $meta->brand_url ?? '',

            address: $meta->address ?? '',
            unsubscribe_url: 'https://example.com/unsubscribe',
            unsubscribe_text: $meta->unsubscribe_text ?? '',

            color_accent: TemplateDefaults::COLOR_ACCENT, // $project->getColorAccent() ?? TemplateDefaults::COLOR_ACCENT,
            color_background: $meta->color_background ?? TemplateDefaults::COLOR_BACKGROUND,
            color_box_background: $meta->color_box_background ?? TemplateDefaults::COLOR_BACKGROUND,

            font_family: $meta->font_family ?? TemplateDefaults::FONT_FAMILY,
            font_size: $meta->font_size ?? TemplateDefaults::FONT_SIZE,
            font_weight: $meta->font_weight ?? TemplateDefaults::FONT_WEIGHT,
            font_weight_heading: $meta->font_weight ?? TemplateDefaults::FONT_WEIGHT_HEADING,
            font_color_on_background: $meta->font_color_on_background ?? TemplateDefaults::FONT_COLOR_ON_BACKGROUND,
            font_color_on_box: $meta->font_color_on_box ?? TemplateDefaults::FONT_COLOR_ON_BACKGROUND,
            font_line_height: $meta->font_line_height ?? TemplateDefaults::FONT_LINE_HEIGHT,

            box_radius: $meta->box_radius ?? TemplateDefaults::BOX_RADIUS,
            box_shadow: $meta->box_shadow ?? TemplateDefaults::BOX_SHADOW,
            box_border: $meta->box_border ?? TemplateDefaults::BOX_BORDER,
        );

        return $this->render($variables);

    }

    public function render(TemplateVariables $variables): string
    {
        return $this->twig->render('newsletter/default.html.twig', (array) $variables);
    }

}
