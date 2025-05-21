<?php

namespace App\Api\Public\Controller\Template;

use App\Api\Public\Input\RetrieveContentHtmlInput;
use App\Api\Public\Input\TemplateRenderWithInput;
use App\Service\Content\ContentService;
use App\Service\Template\TemplateRenderer;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariables;
use Hyvor\Internal\Http\Exceptions\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class TemplateController extends AbstractController
{
    public function __construct(
        private TemplateRenderer $renderer,
        private TemplateService $templateService,
        private ContentService $contentService
    ) {
    }


    #[Route('/template/with', methods: 'POST')]
    public function renderWith(#[MapRequestPayload] TemplateRenderWithInput $input): JsonResponse
    {
        $variables = new TemplateVariables();
        $variablesInput = $input->variables;
        $variablesInput = json_decode($variablesInput, true);

        if (!is_array($variablesInput)) {
            throw new HttpException('Invalid template variables');
        }

        foreach ($variablesInput as $key => $value) {
            if (property_exists($variables, $key)) {
                $variables->$key = $value;
            }
        }

        $html = $this->renderer->render($input->template, $variables);

        return $this->json(['html' => $html]);
    }

    #[Route('/template/default', methods: 'GET')]
    public function defaultTemplate(): JsonResponse
    {
        $rawTemplate = $this->templateService->readDefaultTemplate();
        $defaults = new TemplateVariables();

        return new JsonResponse([
            'template' => $rawTemplate,
            'variables' => $defaults,
        ]);
    }

    #[Route('/template/content', methods: 'POST')]
    public function retrieveContentHtml(#[MapRequestPayload] RetrieveContentHtmlInput $input): JsonResponse
    {
        $contentHtml = $this->contentService->getHtmlFromJson($input->content);
        return new JsonResponse([
            'html' => $contentHtml
        ]);
    }
}
