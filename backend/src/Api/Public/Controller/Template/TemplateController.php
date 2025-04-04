<?php

namespace App\Api\Public\Controller\Template;

use App\Service\Template\TemplateDefaults;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TemplateController extends AbstractController
{

    #[Route('/template/with', methods: 'GET')]
    public function renderWith(): void
    {
        // render the template
    }

    #[Route('/template/default', methods: 'GET')]
    public function defaultTemplate(): JsonResponse
    {
        #$templatePath = $this->getParameter('kernel.project_dir') . '/templates/newsletter/default.html.twig';
        #$rawTemplate = file_get_contents($templatePath);

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
            'template' => '',
            'variables' => "",
        ]);
    }
}
