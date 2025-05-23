<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Template\UpdateTemplateInput;
use App\Api\Console\Input\Template\RenderTemplateInput;
use App\Api\Console\Object\TemplateObject;
use App\Entity\Newsletter;
use App\Service\Newsletter\NewsletterDefaults;
use App\Service\Template\Dto\UpdateTemplateDto;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariables;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class TemplateController extends AbstractController
{
    public function __construct(
        private TemplateService $templateService,
        private HtmlTemplateRenderer $templateRenderer
    ) {
    }

    #[Route('/templates', methods: 'GET')]
    public function getNewsletterTemplate(Newsletter $newsletter): JsonResponse
    {
        $template = $this->templateService->getTemplate($newsletter);

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
        Newsletter $newsletter,
        #[MapRequestPayload] UpdateTemplateInput $input
    ): JsonResponse {
        $templateString = $input->template ?? $this->templateService->readDefaultTemplate();

        $template = $this->templateService->getTemplate($newsletter);

        if ($template) {
            $updates = new UpdateTemplateDto();
            $updates->template = $templateString;
            $template = $this->templateService->updateTemplate($template, $updates);
        } else {
            $template = $this->templateService->createTemplate($newsletter, $templateString);
        }
        return $this->json(new TemplateObject($template));
    }

    #[Route('/templates/render', methods: 'POST')]
    public function renderTemplate(
        Newsletter $newsletter,
        #[MapRequestPayload] RenderTemplateInput $input
    ): JsonResponse {
        // TODO: load from other methods
        $meta = $newsletter->getMeta();

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

            color_accent: $meta->template_color_accent ?? NewsletterDefaults::TEMPLATE_COLOR_ACCENT,
            color_background: $meta->template_color_background ?? NewsletterDefaults::TEMPLATE_COLOR_BACKGROUND,
            color_box: $meta->template_color_box_background ?? NewsletterDefaults::TEMPLATE_COLOR_BACKGROUND,

            font_family: $meta->template_font_family ?? NewsletterDefaults::TEMPLATE_FONT_FAMILY,
            font_size: $meta->template_font_size ?? NewsletterDefaults::TEMPLATE_FONT_SIZE,
            font_weight: $meta->template_font_weight ?? NewsletterDefaults::TEMPLATE_FONT_WEIGHT,
            font_weight_heading: $meta->template_font_weight_heading ?? NewsletterDefaults::TEMPLATE_FONT_WEIGHT_HEADING,
            font_color_on_background: $meta->template_font_color_on_background ?? NewsletterDefaults::TEMPLATE_FONT_COLOR_ON_BACKGROUND,
            font_color_on_box: $meta->template_font_color_on_box ?? NewsletterDefaults::TEMPLATE_FONT_COLOR_ON_BOX,
            font_line_height: $meta->template_font_line_height ?? NewsletterDefaults::TEMPLATE_FONT_LINE_HEIGHT,

            box_radius: $meta->template_box_radius ?? NewsletterDefaults::TEMPLATE_BOX_RADIUS,
            box_shadow: $meta->template_box_shadow ?? NewsletterDefaults::TEMPLATE_BOX_SHADOW,
            box_border: $meta->template_color_box_border ?? NewsletterDefaults::TEMPLATE_BOX_BORDER,
        );

        $html = $this->templateRenderer->render($input->template, $variables);
        return $this->json(['html' => $html]);
    }
}
