<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Entity\Project;
use App\Service\Subscriber\SubscriberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class SubscriberController extends AbstractController
{

    public function __construct(
        private SubscriberService $subscriberService
    )
    {
    }

    #[Route('/subscribers', methods: ['POST'])]
    public function createSubscriber(#[MapRequestPayload] CreateSubscriberInput $input, Project $project): JsonResponse
    {
        // Check list_ids are valid
        $projectLists = $project->getLists();
        $lists = [];
        foreach ($input->list_ids as $listId) {
            $list = $projectLists->filter(fn($list) => $list->getId() === $listId)->first();
            if (!$list) {
                return $this->json(['message' => 'Invalid list id'], 400);
            }
            $lists[] = $list;
        }
        $subscriber = $this->subscriberService->createSubscriber($project, $input->email, $lists);
        return $this->json($subscriber);
    }
}
