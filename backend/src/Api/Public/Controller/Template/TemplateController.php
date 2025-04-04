<?php

namespace App\Api\Public\Controller\Template;

use App\Api\Public\Input\TemplateRenderWithInput;
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
    public function renderWith(#[MapRequestPayload] TemplateRenderWithInput $input): JsonResponse
    {

        $variables = new TemplateVariables();
        $variablesInput = $input->variables;
        $variablesInput = json_decode($variablesInput, true);

        assert(is_array($variablesInput));


        foreach ($variablesInput as $key => $value) {
            if (property_exists($variables, $key)) {
                $variables->$key = $value;
            }
        }

        $html = $this->renderer->render($variables);
        return $this->json(['html' => $html]);
    }

    #[Route('/template/default', methods: 'GET')]
    public function defaultTemplate(): JsonResponse
    {
        $templatePath = $this->getParameter('kernel.project_dir') . '/templates/newsletter/default.html.twig';
        $rawTemplate = file_get_contents($templatePath);
        $defaults = new TemplateVariables();

        return new JsonResponse([
            'template' => $rawTemplate,
            'variables' => $defaults,
        ]);
    }
}
