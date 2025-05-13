<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Template\UpdateTemplateInput;
use App\Api\Console\Input\Template\RenderTemplateInput;
use App\Api\Console\Object\TemplateObject;
use App\Entity\Project;
use App\Service\Template\Dto\UpdateTemplateDto;
use App\Service\Template\TemplateRenderer;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariables;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Template\TemplateDefaults;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class TemplateController extends AbstractController
{
    public function __construct(
        private TemplateService $templateService,
        private TemplateRenderer $templateRenderer
    ) {
    }

    #[Route('/templates', methods: 'GET')]
    public function getProjectTemplate(Project $project): JsonResponse
    {
        $template = $this->templateService->getTemplate($project);

        if (!$template) {
            // Load default template
            return $this->json([
                'template' => $this->templateService->readDefaultTemplate()
            ]);
        }

        return $this->json(new TemplateObject($template));
    }

    #[Route('/templates/update', methods: 'POST')]
    public function updateTemplate(
        Project $project,
        #[MapRequestPayload] UpdateTemplateInput $input
    ): JsonResponse {
        $templateString = $input->template ?? $this->templateService->readDefaultTemplate();

        $template = $this->templateService->getTemplate($project);

        if ($template) {
            $updates = new UpdateTemplateDto();
            $updates->template = $templateString;
            $template = $this->templateService->updateTemplate($template, $updates);
        } else {
            $template = $this->templateService->createTemplate($project, $templateString);
        }
        return $this->json(new TemplateObject($template));
    }

    #[Route('/templates/render', methods: 'POST')]
    public function renderTemplate(
        Project $project,
        #[MapRequestPayload] RenderTemplateInput $input
    ): JsonResponse {
        $meta = $project->getMeta();

        $variables = new TemplateVariables(
            lang: 'en',
            subject: 'Default subject',
            content: 'Default content',

            logo: $meta->template_logo ?? '',
            logo_alt: '',
            brand: '',
            brand_url: '',

            address: '',
            unsubscribe_url: '',
            unsubscribe_text: '',

            color_accent: $meta->template_color_accent ?? TemplateDefaults::COLOR_ACCENT,
            color_background: $meta->template_color_background ?? TemplateDefaults::COLOR_BACKGROUND,
            color_box_background: $meta->template_color_box_background ?? TemplateDefaults::COLOR_BACKGROUND,

            font_family: $meta->template_font_family ?? TemplateDefaults::FONT_FAMILY,
            font_size: $meta->template_font_size ?? TemplateDefaults::FONT_SIZE,
            font_weight: $meta->template_font_weight ?? TemplateDefaults::FONT_WEIGHT,
            font_weight_heading: $meta->template_font_weight_heading ?? TemplateDefaults::FONT_WEIGHT_HEADING,
            font_color_on_background: $meta->template_font_color_on_background ?? TemplateDefaults::FONT_COLOR_ON_BACKGROUND,
            font_color_on_box: $meta->template_font_color_on_box ?? TemplateDefaults::FONT_COLOR_ON_BOX,
            font_line_height: $meta->template_font_line_height ?? TemplateDefaults::FONT_LINE_HEIGHT,

            box_radius: $meta->template_box_radius ?? TemplateDefaults::BOX_RADIUS,
            box_shadow: $meta->template_color_box_shadow ?? TemplateDefaults::BOX_SHADOW,
            box_border: $meta->template_color_box_border ?? TemplateDefaults::BOX_BORDER,
        );

        $html = $this->templateRenderer->renderAll($input->template, $variables);
        return $this->json(['html' => $html]);
    }
}
