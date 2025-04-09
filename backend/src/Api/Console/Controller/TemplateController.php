<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Template\CreateTemplateInput;
use App\Api\Console\Object\TemplateObject;
use App\Entity\Project;
use App\Service\Template\TemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class TemplateController extends AbstractController
{
    public function __construct(
        private TemplateService $templateService
    )
    {
    }

    #[Route('/templates', methods: 'GET')]
    public function getProjectTemplate(Project $project): JsonResponse
    {
        $template = $this->templateService->getTemplate($project);

        if (!$template) {
            // Load default template
            $templatePath = $this->getParameter('kernel.project_dir') . '/templates/newsletter/default.html.twig';
            $rawTemplate = file_get_contents($templatePath);
            return $this->json([
                'template' => $rawTemplate,
                ]
            );
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
        // TODO: check if template already exists
        $template = $this->templateService->createTemplate($project, $input->template);
        return $this->json(new TemplateObject($template));
    }
}
