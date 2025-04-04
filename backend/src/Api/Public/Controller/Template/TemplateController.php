<?php

namespace App\Api\Public\Controller\Template;

use App\Api\Public\Input\TemplateVariablesInput;
use App\Service\Template\TemplateDefaults;
use App\Service\Template\TemplateRenderer;
use App\Service\Template\TemplateVariables;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class TemplateController extends AbstractController
{
    public function __construct(
        private TemplateRenderer $renderer,
    )
    {
    }


    #[Route('/template/with', methods: 'POST')]
    public function renderWith(#[MapRequestPayload] TemplateVariablesInput $input): JsonResponse
    {
        $templateVariable = new TemplateVariables(
            lang: $input->lang,
            subject: $input->subject,
            content: $input->content,
            logo: '',
            logo_alt: '',
            brand: '',
            brand_url: '',
            address: '',
            unsubscribe_url: '',
            unsubscribe_text: '',
            color_accent: $input->colorAccent,
            color_background: $input->colorBackground,
            color_box_background: $input->colorBoxBackground,
            color_box_radius: $input->colorBoxRadius,
            color_box_shadow: $input->colorBoxShadow,
            color_box_border: $input->colorBoxBorder,
            font_family: $input->fontFamily,
            font_size: $input->fontSize,
            font_weight: $input->fontWeight,
            font_weight_heading: $input->fontWeightHeading,
            font_color_on_background: $input->fontColorOnBackground,
            font_color_on_box: $input->fontColorOnBox,
            font_line_height: $input->fontLineHeight
        );

        $html = $this->renderer->render($templateVariable);
        return $this->json(['html' => $html]);
    }

    #[Route('/template/default', methods: 'GET')]
    public function defaultTemplate(): JsonResponse
    {
        $templatePath = $this->getParameter('kernel.project_dir') . '/templates/newsletter/default.html.twig';
        $rawTemplate = file_get_contents($templatePath);

        $defaultsVariables = [
            'color_accent' => TemplateDefaults::COLOR_ACCENT,
            'color_background' => TemplateDefaults::COLOR_BACKGROUND,
            'color_box_background' => TemplateDefaults::COLOR_BOX_BACKGROUND,
            'color_box_radius' => TemplateDefaults::COLOR_BOX_RADIUS,
            'color_box_shadow' => TemplateDefaults::COLOR_BOX_SHADOW,
            'color_box_border' => TemplateDefaults::COLOR_BOX_BORDER,
            'font_family' => TemplateDefaults::FONT_FAMILY,
            'font_size' => TemplateDefaults::FONT_SIZE,
            'font_weight' => TemplateDefaults::FONT_WEIGHT,
            'font_weight_heading' => TemplateDefaults::FONT_WEIGHT_HEADING,
            'font_color_on_background' => TemplateDefaults::FONT_COLOR_ON_BACKGROUND,
            'font_color_on_box' => TemplateDefaults::FONT_COLOR_ON_BOX,
            'font_line_height' => TemplateDefaults::FONT_LINE_HEIGHT,
        ];

        return new JsonResponse([
            'template' => $rawTemplate,
            'variables' => json_encode($defaultsVariables, JSON_PRETTY_PRINT),
        ]);
    }
}
