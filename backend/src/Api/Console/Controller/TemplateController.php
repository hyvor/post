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
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
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
        $variables = new TemplateVariables();
        $meta = $project->getMeta();
        // TODO: just hardcode template variables from meta
        // TODO: set subject and content to a default value.
        foreach (get_object_vars($meta) as $key => $value) {
            if (property_exists($variables, $key)) {
                $variables->$key = $value;
            }
        }

        $html = $this->templateRenderer->renderAll($input->template, $variables);
        return $this->json(['html' => $html]);
    }
}
