<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Object\ProjectObject;
use App\Api\Console\Object\StatsObject;
use App\Api\Console\Object\ListObject;
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

        $projects = $this->projectService->getProjectsOfUser($user->id);
        $projects = array_map(fn(Project $project) => new ProjectObject($project), $projects);

        return new JsonResponse([
            'projects' => $projects
        ]);
    }

    #[Route('/init/project',  methods: 'GET')]
    public function initProject(Project $project): JsonResponse
    {
        $project_stats = $this->projectService->getProjectStats($project);
        $lists = $project->getLists();
        return new JsonResponse([
            'project' => new ProjectObject($project),
            'lists' => array_map(fn($list) => new ListObject($list), $lists->toArray()),
            'stats' => new StatsObject(
                $project_stats[0],
                $project_stats[1],
                $project_stats[2]
            )
        ]);
    }

}
