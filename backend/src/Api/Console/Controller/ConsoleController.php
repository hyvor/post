<?php

declare(strict_types=1);

namespace App\Api\Console\Controller;

use App\Api\Console\Object\ListObject;
use App\Api\Console\Object\NewsletterListObject;
use App\Api\Console\Object\NewsletterObject;
use App\Api\Console\Object\StatsObject;
use App\Api\Console\Object\SubscriberMetadataDefinitionObject;
use App\Entity\Newsletter;
use App\Repository\ListRepository;
use App\Service\Newsletter\NewsletterDefaults;
use App\Service\Newsletter\NewsletterService;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ConsoleController extends AbstractController
{

    use HasHyvorUser;

    public function __construct(
        private NewsletterService $projectService,
        private ListRepository $listRepository,
        private InternalConfig $internalConfig,
        private SubscriberMetadataService $subscriberMetadataService,
    ) {
    }

    #[Route('/init', methods: 'GET')]
    public function initConsole(): JsonResponse
    {
        $user = $this->getUser();
        assert($user instanceof AuthUser);

        $projectsUsers = $this->projectService->getProjectsOfUser($user->id);
        $projects = array_map(
            fn(array $pair) => new NewsletterListObject($pair['project'], $pair['user']),
            $projectsUsers
        );

        return new JsonResponse([
            'projects' => $projects,
            'config' => [
                'hyvor' => [
                    'instance' => $this->internalConfig->getInstance(),
                ],
                // 'template_defaults' => TemplateDefaults::getAll(),
                'newsletter_defaults' => NewsletterDefaults::getAll(),
            ],
        ]);
    }

    #[Route('/init/project', methods: 'GET')]
    public function initProject(Newsletter $project): JsonResponse
    {
        $projectStats = $this->projectService->getProjectStats($project);
        $lists = $this->listRepository->findBy(
            [
                'project' => $project,
                'deleted_at' => null,
            ]
        );

        $subscriberMetadataDefinitions = $this->subscriberMetadataService->getMetadataDefinitions($project);

        return new JsonResponse([
            'project' => new NewsletterObject($project),
            'lists' => array_map(fn($list) => new ListObject($list), $lists),
            'subscriber_metadata_definitions' => array_map(fn($def) => new SubscriberMetadataDefinitionObject($def),
                $subscriberMetadataDefinitions),
            'stats' => new StatsObject(
                $projectStats[0],
                $projectStats[1],
                $projectStats[2]
            )
        ]);
    }

}
