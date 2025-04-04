<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Object\IssueObject;
use App\Api\Console\Object\ProjectObject;
use App\Api\Console\Object\StatsObject;
use App\Api\Console\Object\ListObject;
use App\Entity\Project;
use App\Repository\IssueRepository;
use App\Repository\ListRepository;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Project\ProjectService;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ConsoleController extends AbstractController
{

    public function __construct(
        private ProjectService $projectService,
        private ListRepository $listRepository,
        private IssueRepository $issueRepository,
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
        $projectStats = $this->projectService->getProjectStats($project);
        $lists = $this->listRepository->findBy(
            [
                'project' => $project,
                'deleted_at' => null,
            ]
        );
        return new JsonResponse([
            'project' => new ProjectObject($project),
            'lists' => array_map(fn($list) => new ListObject($list), $lists),
            'stats' => new StatsObject(
                $projectStats[0],
                $projectStats[1],
                $projectStats[2]
            )
        ]);
    }

}
