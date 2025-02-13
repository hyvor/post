<?php

namespace App\Api\Console\Controller;

use App\Api\Console\InputObject\CreateProjectInputObject;
use App\Api\Console\OutputObject\ProjectOutputObject;
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
    public function createProject(#[MapRequestPayload] CreateProjectInputObject $input): JsonResponse
    {
        $project = $this->projectService->createProject($input->name);
        return $this->json(new ProjectOutputObject($project));
    }

    #[Route('/project', name: 'delete_project', methods: ['DELETE'])]
    public function deleteProject(int $id): JsonResponse
    {
        $this->projectService->deleteProject($id);
        return $this->json(['message' => 'Project deleted']);
    }
}
