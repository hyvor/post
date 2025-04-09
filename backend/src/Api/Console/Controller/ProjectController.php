<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Project\CreateProjectInput;
use App\Api\Console\Input\Project\UpdateProjectMetaInput;
use App\Api\Console\Object\ProjectObject;
use App\Entity\Project;
use App\Service\Project\Dto\UpdateProjectMetaDto;
use App\Service\Project\ProjectService;
use Hyvor\Internal\Auth\AuthUser;
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

    #[Route('/projects', methods: 'GET', condition: 'request.headers.get("X-Project-Id") === null')]
    public function getUserAllProjects(): JsonResponse
    {
        $user = $this->getUser();
        assert($user instanceof AuthUser);
        $projects = $this->projectService->getProjectsOfUser($user->id);
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

    #[Route('/projects',  methods: 'GET', condition: 'request.headers.get("X-Project-Id") !== null')]
    public function getProjectById(Project $project): JsonResponse
    {
        return $this->json(new ProjectObject($project));
    }

    #[Route('/projects', methods: 'DELETE')]
    public function deleteProject(Project $project): JsonResponse
    {
        $this->projectService->deleteProject($project);
        return $this->json([]);
    }

    #[Route('/projects/meta', methods: 'POST')]
    public function updateProjectMeta(
        Project $project,
        #[MapRequestPayload] UpdateProjectMetaInput $input
    ): JsonResponse
    {
        $updatesMeta = new UpdateProjectMetaDto();

        if ($input->templateColorAccent)
            $updatesMeta->templateColorAccent = $input->templateColorAccent;

        if ($input->templateColorBackground)
            $updatesMeta->templateColorBackground = $input->templateColorBackground;

        if ($input->templateColorBoxBackground)
            $updatesMeta->templateColorBoxBackground = $input->templateColorBoxBackground;

        if ($input->templateColorBoxShadow)
            $updatesMeta->templateColorBoxShadow = $input->templateColorBoxShadow;

        if ($input->templateColorBoxBorder)
            $updatesMeta->templateColorBoxBorder = $input->templateColorBoxBorder;

        if ($input->templateFontFamily)
            $updatesMeta->templateFontFamily = $input->templateFontFamily;

        if ($input->templateFontSize)
            $updatesMeta->templateFontSize = $input->templateFontSize;

        if ($input->templateFontWeight)
            $updatesMeta->templateFontWeight = $input->templateFontWeight;

        if ($input->templateFontWeightHeading)
            $updatesMeta->templateFontWeightHeading = $input->templateFontWeightHeading;

        if ($input->templateFontColorOnBackground)
            $updatesMeta->templateFontColorOnBackground = $input->templateFontColorOnBackground;

        if ($input->templateFontColorOnBox)
            $updatesMeta->templateFontColorOnBox = $input->templateFontColorOnBox;

        if ($input->templateFontLineHeight)
            $updatesMeta->templateFontLineHeight = $input->templateFontLineHeight;

        if ($input->templateBoxRadius)
            $updatesMeta->templateBoxRadius = $input->templateBoxRadius;

        if ($input->templateLogo)
            $updatesMeta->templateLogo = $input->templateLogo;

        $project = $this->projectService->updateProjectMeta($project, $updatesMeta);

        return $this->json(new ProjectObject($project));
    }
}
