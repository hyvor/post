<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Project\CreateProjectInput;
use App\Api\Console\Input\Project\UpdateProjectInput;
use App\Api\Console\Object\ProjectObject;
use App\Entity\Project;
use App\Service\Project\Dto\UpdateProjectMetaDto;
use App\Service\Project\ProjectService;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\UnicodeString;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;

final class ProjectController extends AbstractController
{
    use HasHyvorUser;

    public function __construct(
        private ProjectService $projectService
    )
    {
    }

    #[Route('/projects', methods: 'GET', condition: 'request.headers.get("X-Project-Id") === null')]
    public function getUserAllProjects(): JsonResponse
    {
        $user = $this->getHyvorUser();
        $projectsUsers = $this->projectService->getProjectsOfUser($user->id);
        $projects = array_map(
            fn(array $pair) => new ProjectObject($pair['project'], $pair['user'], $user),
            $projectsUsers
        );
        return $this->json($projects);
    }

    #[Route('/projects', methods: 'POST')]
    public function createProject(#[MapRequestPayload] CreateProjectInput $input): JsonResponse
    {
        $user = $this->getHyvorUser();

        $project = $this->projectService->createProject($user->id, $input->name);
        $projectUser = $this->projectService->getProjectUser($project, $user->id);
        return $this->json(new ProjectObject($project, $projectUser, $user));
    }

    #[Route('/projects',  methods: 'GET', condition: 'request.headers.get("X-Project-Id") !== null')]
    public function getProjectById(Project $project): JsonResponse
    {
        $user = $this->getHyvorUser();

        $projectUser = $this->projectService->getProjectUser($project, $user->id);
        return $this->json(new ProjectObject($project, $projectUser, $user));
    }

    #[Route('/projects', methods: 'DELETE')]
    public function deleteProject(Project $project): JsonResponse
    {
        $this->projectService->deleteProject($project);
        return $this->json([]);
    }

    #[Route('/projects', methods: 'PATCH')]
    public function updateProject(
        Project $project,
        #[MapRequestPayload] UpdateProjectInput $input
    ): JsonResponse
    {
        $user = $this->getHyvorUser();
        // later we may need to add functions to update project non-meta properties

        $updatesMeta = new UpdateProjectMetaDto();
        $properties = $input->getSetProperties();
        foreach ($properties as $property) {
            $cased = new UnicodeString($property)->camel();
            $updatesMeta->{$cased} = $input->{$property};
        }

        $project = $this->projectService->updateProjectMeta($project, $updatesMeta);
        $projectUser = $this->projectService->getProjectUser($project, $user->id);

        return $this->json(new ProjectObject($project, $projectUser, $user));
    }
}
