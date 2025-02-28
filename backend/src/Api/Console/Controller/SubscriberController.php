<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Input\Subscriber\UpdateSubscriberInput;
use App\Api\Console\Object\SubscriberObject;
use App\Entity\Project;
use App\Entity\Subscriber;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\SubscriberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class SubscriberController extends AbstractController
{

    public function __construct(
        private SubscriberService $subscriberService,
        private NewsletterListService $newsletterListService
    )
    {
    }

    #[Route('/subscribers', methods: 'GET')]
    public function getSubscribers(Request $request, Project $project): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);
        $subscribers = $this->subscriberService->getSubscribers($project, $limit, $offset);
        return $this->json($subscribers->map(fn($subscriber) => new SubscriberObject($subscriber)));
    }

    #[Route('/subscribers', methods: ['POST'])]
    public function createSubscriber(#[MapRequestPayload] CreateSubscriberInput $input, Project $project): JsonResponse
    {
        $lists = $this->newsletterListService->isListsAvailable($project, $input->list_ids);
        $subscriber = $this->subscriberService->createSubscriber(
            $project,
            $input->email,
            $lists,
            $input->status ?? 'subscribed',
            $input->source ?? 'console',
            $input->subscribe_ip,
            $input->subscribed_at,
            $input->unsubscribed_at,
        );

        return $this->json(new SubscriberObject($subscriber));
    }

    #[Route('/subscribers/{id}', methods: 'PATCH')]
    public function updateSubscriber(
        Subscriber $subscriber,
        Project $project,
        #[MapRequestPayload] UpdateSubscriberInput $input
    ): JsonResponse
    {
        $lists = $this->newsletterListService->isListsAvailable($project, $input->list_ids);
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
