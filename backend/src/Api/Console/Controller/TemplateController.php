<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Template\UpdateTemplateInput;
use App\Api\Console\Input\Template\RenderTemplateInput;
use App\Api\Console\Object\TemplateObject;
use App\Entity\Newsletter;
use App\Service\Content\ContentDefaultStyle;
use App\Service\Template\Dto\UpdateTemplateDto;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class TemplateController extends AbstractController
{
    public function __construct(
        private TemplateService         $templateService,
        private TemplateVariableService $templateVariableService,
        private HtmlTemplateRenderer    $htmlTemplateRenderer,
        private ContentDefaultStyle     $contentDefaultStyle,
    )
    {
    }

    #[Route('/templates', methods: 'GET')]
    #[ScopeRequired(Scope::TEMPLATES_READ)]
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

    #[Route('/templates', methods: 'PATCH')]
    #[ScopeRequired(Scope::TEMPLATES_WRITE)]
    public function updateTemplate(
        Newsletter                               $newsletter,
        #[MapRequestPayload] UpdateTemplateInput $input
    ): JsonResponse
    {
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
    #[ScopeRequired(Scope::TEMPLATES_READ)]
    public function renderTemplate(
        Newsletter                               $newsletter,
        #[MapRequestPayload] RenderTemplateInput $input
    ): JsonResponse
    {
        $subject = 'Hyvor Post Default Email';
        $defaultContentHtml = $this->contentDefaultStyle->html();

        $variables = $this->templateVariableService->variablesFromNewsletter($newsletter);
        $variables->subject = $subject;
        $variables->content = $defaultContentHtml;

        $template = $input->template ?? $this->templateService->getTemplateStringFromNewsletter($newsletter);

        $html = $this->htmlTemplateRenderer->render($template, $variables);

        return $this->json(['html' => $html]);
    }
}
