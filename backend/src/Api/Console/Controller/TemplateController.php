<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Template\CreateTemplateInput;
use App\Api\Console\Object\TemplateObject;
use App\Entity\Project;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariables;
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
        return $this->json([
            'template' => '',
            'variables' => ''
        ]);
    }

    #[Route('/templates', methods: 'POST')]
    public function createTemplate(
        Project $project,
        #[MapRequestPayload] CreateTemplateInput $input
    ): JsonResponse
    {
        $template = $this->templateService->createTemplate($project, $input->template);
        return $this->json(new TemplateObject($template));
    }
}
