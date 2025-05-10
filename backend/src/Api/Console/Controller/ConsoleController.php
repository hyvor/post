<?php declare(strict_types=1);

namespace App\Api\Console\Controller;

use App\Api\Console\Object\ListObject;
use App\Api\Console\Object\ProjectListObject;
use App\Api\Console\Object\ProjectObject;
use App\Api\Console\Object\StatsObject;
use App\Entity\Project;
use App\Repository\ListRepository;
use App\Service\Project\ProjectDefaults;
use App\Service\Project\ProjectService;
use App\Service\Template\TemplateDefaults;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ConsoleController extends AbstractController
{

    use HasHyvorUser;

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
            fn(array $pair) => new ProjectListObject($pair['project'], $pair['user']),
            $projectsUsers
        );

        return new JsonResponse([
            'projects' => $projects,
            'config' => [
                'template_defaults' => TemplateDefaults::getAll(),
                'project_defaults' => ProjectDefaults::getAll(),
            ],
        ]);
    }

    #[Route('/init/project',  methods: 'GET')]
    public function initProject(Project $project): JsonResponse
    {
        $user = $this->getHyvorUser();

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
