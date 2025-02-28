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
use Symfony\Component\HttpKernel\Exception\HttpException;
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
    public function getSubscribers(Project $project): JsonResponse
    {
        // TODO: implement pagination (limit, offset)
        $subscribers = $this->subscriberService->getSubscribers($project);
        return $this->json($subscribers->map(fn($subscriber) => new SubscriberObject($subscriber)));
    }

    #[Route('/subscribers', methods: ['POST'])]
    public function createSubscriber(#[MapRequestPayload] CreateSubscriberInput $input, Project $project): JsonResponse
    {
        // Check list_ids are valid
        $projectLists = new ArrayCollection($this->listRepository->findBy(['project' => $project])); // 2000
        $lists = [];
        foreach ($input->list_ids as $listId) {
            $list = $projectLists->filter(fn($list) => $list->getId() === $listId)->first();
            if (!$list) {
                throw new HttpException(422, 'Invalid list id: ' . $listId);
            }
            $lists[] = $list;
        }

        /**
         * isListsAvailable($project, $input->list_ids)
         * SELECT id FROM lists WHERE project_id = ? AND id IN (?, ?, ?)
         */

        $subscriber = $this->subscriberService->createSubscriber($project, $input->email, $lists);

        return $this->json(new SubscriberObject($subscriber));
    }

    #[Route('/subscribers/{id}', methods: 'PATCH')]
    public function updateSubscriber(
        Subscriber $subscriber,
        Project $project,
        #[MapRequestPayload] UpdateSubscriberInput $input
    ): JsonResponse
    {
        $projectLists = new ArrayCollection($this->listRepository->findBy(['project' => $project]));
        $lists = [];
        foreach ($input->list_ids as $listId) {
            $list = $projectLists->filter(fn($list) => $list->getId() === $listId)->first();
            if (!$list) {
                return $this->json(['message' => 'Invalid list id'], 400);
            }
            $lists[] = $list;
        }
        $subscriber = $this->subscriberService->updateSubscriber(
            $subscriber,
            $input->email ?? $subscriber->getEmail(),
            $lists,
            $input->status ?? $subscriber->getStatus()->value ?? 'pending',
        );
        return $this->json(new SubscriberObject($subscriber));
    }

    #[Route('/subscribers/{id}', methods: 'DELETE')]
    public function deleteSubscriber(Subscriber $subscriber): JsonResponse
    {
        $this->subscriberService->deleteSubscriber($subscriber);
        return $this->json([]);
    }
}
