<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Input\Subscriber\UpdateSubscriberInput;
use App\Api\Console\Object\SubscriberObject;
use App\Entity\Project;
use App\Entity\Subscriber;
use App\Repository\ListRepository;
use App\Service\NewsletterList\NewsletterListService;
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
        private NewsletterListService $newsletterListService
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
        $lists = $this->newsletterListService->isListsAvailable($project, $input->list_ids);
        $subscriber = $this->subscriberService->createSubscriber(
            $project,
            $input->email,
            $lists,
            $input->status ?? 'pending',
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
