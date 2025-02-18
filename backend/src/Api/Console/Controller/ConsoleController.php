<?php

namespace App\Api\Console\Controller;

use App\Service\NewsletterList\NewsletterListService;
use App\Service\Project\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ConsoleController extends AbstractController
{

    public function __construct(
        private ProjectService $projectService
    )
    {
    }

    #[Route('/init', methods: 'GET')]
    public function initConsole(): JsonResponse
    {
        $projects = $this->projectService->getProjects();
        return $this->json([
            'projects' => $projects
        ]);
    }

    /*
    public function initProject()
    {

    }
    */
}
