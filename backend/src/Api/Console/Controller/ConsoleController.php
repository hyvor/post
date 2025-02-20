<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Object\ProjectObject;
use App\Entity\Project;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Project\ProjectService;
use Hyvor\Internal\Auth\AuthUser;
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
        $user = $this->getUser();
        assert($user instanceof AuthUser);

        $projects = $this->projectService->getProjects($user->id);
        $projects = array_map(fn(Project $project) => new ProjectObject($project), $projects);

        return new JsonResponse([
            'projects' => $projects
        ]);
    }

    #[Route('/init/project',  methods: 'GET')]
    public function initProject(Project $project): JsonResponse
    {
        return new JsonResponse([
            'project' => new ProjectObject($project),
        ]);
    }

}
