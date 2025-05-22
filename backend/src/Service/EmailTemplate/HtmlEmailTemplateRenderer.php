<?php

namespace App\Service\EmailTemplate;

use App\Service\Content\ContentService;
use App\Entity\Issue;
use App\Entity\Project;
use App\Service\Project\ProjectDefaults;
use Twig\Environment;

class HtmlEmailTemplateRenderer
{

    const DEFAULT_CONTENT = <<<JSON
{
    "type": "doc"
}
JSON;


    public function __construct(
        private Environment $twig,
        private ContentService $contentService,
        private EmailTemplateService $templateService,
    ) {
    }

    public function getTemplateVariablesFromProject(Project $project): EmailTemplateVariables
    {
        $meta = $project->getMeta();

        return new EmailTemplateVariables(
            lang: 'en',
            subject: '',
            content: '',

            logo: $meta->template_logo ?? '',
            logo_alt: $meta->template_logo_alt ?? '',
            brand: $meta->brand ?? $project->getName(),
            brand_url: $meta->brand_url ?? '',

            address: $meta->address ?? '',
            unsubscribe_url: '',
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
    }

    public function renderFromIssue(Issue $issue): string
    {
        return $this->renderFromSubjectAndContent(
            $issue->getProject(),
            (string)$issue->getSubject(),
            $this->contentService->getHtmlFromJson($issue->getContent() ?? self::DEFAULT_CONTENT),
        );
    }

    public function renderFromSubjectAndContent(
        Project $project,
        string $subject,
        string $content,
    ): string {
        $variables = $this->getTemplateVariablesFromProject($project);
        $variables->subject = $subject;
        $variables->content = $content;

        $template = $this->templateService->getTemplateStringFromProject($project);

        return $this->render($template, $variables);
    }

    public function render(string $template, EmailTemplateVariables $variables): string
    {
        $template = $this->twig->createTemplate($template);
        return $template->render((array)$variables);
    }

}
