<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Object\SubscriberObject;
use App\Entity\Project;
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
}
