<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\CreateProjectInput;
use App\Api\Console\Object\ProjectObject;
use App\Entity\Project;
use App\Service\Project\ProjectService;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Bundle\Security\UserRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectController extends AbstractController
{

    public function __construct(
        private ProjectService $projectService
    )
    {
    }

    #[Route('/projects', methods: 'GET', condition: 'request.headers.get("X-Resource-Id") === null')]
    public function getProjects(): JsonResponse
    {
        // TODO: only return projects of the current user
        $projects = $this->projectService->getProjects();
        return $this->json(array_map(fn (Project $project) => new ProjectObject($project), $projects));
    }

    #[Route('/projects', methods: 'POST')]
    public function createProject(#[MapRequestPayload] CreateProjectInput $input): JsonResponse
    {
        $user = $this->getUser();
        assert($user instanceof AuthUser);

        $project = $this->projectService->createProject($user->id, $input->name);
        return $this->json(new ProjectObject($project));
    }

    #[Route('/projects',  methods: 'GET', condition: 'request.headers.get("X-Resource-Id") !== null')]
    public function getById(Project $project): JsonResponse
    {
        return $this->json(new ProjectObject($project));
    }

    #[Route('/projects', methods: 'DELETE')]
    public function deleteProject(Project $project): JsonResponse
    {
        $this->projectService->deleteProject($project);
        return $this->json(['message' => 'Project deleted']);
    }

}
