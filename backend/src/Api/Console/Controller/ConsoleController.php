<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Object\ProjectObject;
use App\Entity\Project;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Project\ProjectService;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ConsoleController extends AbstractController
{

    public function __construct(
        private ProjectService $projectService
    )
    {
    }

    #[Route('/init', methods: 'GET')]
    public function initConsole(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();
        assert($user instanceof AuthUser);

        $projects = $this->projectService->getProjects($user->id);
        $json = $serializer->serialize(['projects' => $projects], 'json', ['groups' => 'project:list']);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/init/project',  methods: 'GET', condition: 'request.headers.get("X-Resource-Id") !== null')]
    public function initProject(Project $project, SerializerInterface $serializer): JsonResponse
    {
        $project = $this->projectService->getProject($project->getId());
        $json = $serializer->serialize(['project' => $project], 'json', ['groups' => ['project:list', 'project:details']]);
project
        return new JsonResponse($json, 200, [], true);
    }

}
