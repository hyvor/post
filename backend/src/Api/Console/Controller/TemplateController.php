<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Template\CreateTemplateInput;
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
    )
    {
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

    // TODO: Handle update. Change URL to /templates/update
    #[Route('/templates/update', methods: 'POST')]
    public function createTemplate(
        Project $project,
        #[MapRequestPayload] CreateTemplateInput $input
    ): JsonResponse
    {
        $template = $this->templateService->getTemplate($project);

        if ($template)
        {
            $updates = new UpdateTemplateDto();
            if (!$input->hasProperty('template')) {
                throw new BadRequestException('Template should not be null');
            }
            $updates->template = $input->template;
            $template = $this->templateService->updateTemplate($template, $updates);
        }
        else {
            $template = $this->templateService->createTemplate($project);
        }
        return $this->json(new TemplateObject($template));
    }

    #[Route('/templates/render', methods: 'POST')]
    public function renderTemplate(
        Project $project,
        #[MapRequestPayload] RenderTemplateInput $input
    ): JsonResponse
    {
        $variables = new TemplateVariables();
        $meta = $project->getMeta();
        foreach (get_object_vars($meta) as $key => $value) {
            if (property_exists($variables, $key)) {
                $variables->$key = $value;
            }
        }

        $html = $this->templateRenderer->renderAll($input->template, $variables);
        return $this->json(['html' => $html]);
    }
}
