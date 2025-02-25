<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Input\Subscriber\UpdateSubscriberInput;
use App\Api\Console\Object\SubscriberObject;
use App\Entity\Project;
use App\Entity\Subscriber;
use App\Repository\ListRepository;
use App\Service\Subscriber\SubscriberService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class SubscriberController extends AbstractController
{

    public function __construct(
        private SubscriberService $subscriberService,
        private ListRepository $listRepository,
    )
    {
    }

    #[Route('/subscribers', methods: 'GET')]
    public function getProjectSubscribers(Project $project): JsonResponse
    {
        $subscribers = $this->subscriberService->getSubscribers($project);
        return $this->json(array_map(fn($subscriber) => new SubscriberObject($subscriber), $subscribers));
    }

    #[Route('/subscribers', methods: ['POST'])]
    public function createSubscriber(#[MapRequestPayload] CreateSubscriberInput $input, Project $project): JsonResponse
    {
        // Check list_ids are valid
        $projectLists = new ArrayCollection($this->listRepository->findBy(['project' => $project]));
        $lists = [];
        foreach ($input->list_ids as $listId) {
            $list = $projectLists->filter(fn($list) => $list->getId() === $listId)->first();
            if (!$list) {
                return $this->json(['message' => 'Invalid list id'], 400);
            }
            $lists[] = $list;
        }
        $subscriber = $this->subscriberService->createSubscriber($project, $input->email, $lists);

        return $this->json(new SubscriberObject($subscriber));
    }

    #[Route('/subscribers/{id}', methods: 'PATCH')]
    public function updateSubscriber(
        Subscriber $subscriber,
        #[MapRequestPayload] UpdateSubscriberInput $input
    ): JsonResponse
    {
        $subscriber = $this->subscriberService->updateSubscriber(
            $subscriber,
            $input->email ?? $subscriber->getEmail(),
            $input->list_ids ?? $subscriber->getLists()->map(fn($list) => $list->getId())->toArray(),
            $input->status ?? $subscriber->getStatus()->value ?? 'pending',
        );
        return $this->json(new SubscriberObject($subscriber));
    }
}
