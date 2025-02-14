<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\CreateProjectInput;
use App\Api\Console\Object\ProjectObject;
use App\Entity\Project;
use App\Service\Project\ProjectService;
use Illuminate\Support\Js;
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

    #[Route('/project/{id}')]
    public function getById(int $id): JsonResponse
    {
        $project = $this->projectService->getProject($id);
        if (!$project) {
            return $this->json(['message' => 'Project not found'], 404);
        }
        return $this->json(new ProjectObject($project));
    }

    #[Route('/project', name: 'create_project', methods: ['POST'])]
    public function createProject(#[MapRequestPayload] CreateProjectInput $input): JsonResponse
    {
        $project = $this->projectService->createProject($input->name);
        return $this->json(new ProjectObject($project));
    }

    #[Route('/projects/{id}', name: 'delete_project', methods: ['DELETE'])]
    public function deleteProject(int $id): JsonResponse
    {
        $project = $this->projectService->getProject($id);
        if (!$project) {
            return $this->json(['message' => 'Project not found'], 404);
        }
        $this->projectService->deleteProject($project);
        return $this->json(['message' => 'Project deleted']);
    }

    #[Route('/projects', name: 'list_projects', methods: ['GET'])]
    public function listProjects(): JsonResponse
    {
        $projects = $this->projectService->listProjects();
        return $this->json(array_map(fn (Project $project) => new ProjectObject($project), $projects));
    }
}
