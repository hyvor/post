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
        // TODO: just return the templates
        return $this->json([
            'template' => '',
        ]);
    }

    // TODO: Handle update. Change URL to /templates/update
    #[Route('/templates', methods: 'POST')]
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
