<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Object\IssueObject;
use App\Api\Console\Object\ProjectListObject;
use App\Api\Console\Object\ProjectObject;
use App\Api\Console\Object\StatsObject;
use App\Api\Console\Object\ListObject;
use App\Entity\Project;
use App\Repository\IssueRepository;
use App\Repository\ListRepository;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Project\ProjectService;
use App\Service\Template\TemplateDefaults;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ConsoleController extends AbstractController
{

    public function __construct(
        private ProjectService $projectService,
        private ListRepository $listRepository,
    )
    {
    }

    #[Route('/init', methods: 'GET')]
    public function initConsole(): JsonResponse
    {
        $user = $this->getUser();
        assert($user instanceof AuthUser);

        $projectsUsers = $this->projectService->getProjectsOfUser($user->id);
        $projects = array_map(
            fn(array $pair) => new ProjectListObject($pair['project'], $pair['user']->getRole()),
            $projectsUsers
        );

        return new JsonResponse([
            'projects' => $projects,
            'config' => [
                'template_defaults' => TemplateDefaults::getAll()
            ],
        ]);
    }

    #[Route('/init/project',  methods: 'GET')]
    public function initProject(Project $project): JsonResponse
    {
        $user = $this->getUser();
        assert($user instanceof AuthUser);

        $projectStats = $this->projectService->getProjectStats($project);
        $lists = $this->listRepository->findBy(
            [
                'project' => $project,
                'deleted_at' => null,
            ]
        );
        $projectUser = $this->projectService->getProjectUser($project, $user->id);

        return new JsonResponse([
            'project' => new ProjectListObject($project, $projectUser->getRole()),
            'lists' => array_map(fn($list) => new ListObject($list), $lists),
            'stats' => new StatsObject(
                $projectStats[0],
                $projectStats[1],
                $projectStats[2]
            )
        ]);
    }

}
