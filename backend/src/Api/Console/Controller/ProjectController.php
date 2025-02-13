<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\CreateProjectInput;
use App\Api\Console\Object\ProjectObject;
use App\Service\Project\ProjectService;
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

    #[Route('/project', name: 'create_project', methods: ['POST'])]
    public function createProject(#[MapRequestPayload] CreateProjectInput $input): JsonResponse
    {
        $project = $this->projectService->createProject($input->name);
        return $this->json(new ProjectObject($project));
    }

    #[Route('/project', name: 'delete_project', methods: ['DELETE'])]
    public function deleteProject(int $id): JsonResponse
    {
        // TODO: Check if the project is there
        $this->projectService->deleteProject($id);
        return $this->json(['message' => 'Project deleted']);
    }
}
