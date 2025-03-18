<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Input\Subscriber\UpdateSubscriberInput;
use App\Api\Console\Object\SubscriberObject;
use App\Entity\Project;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\SubscriberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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
    public function getSubscribers(Request $request, Project $project): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $status = $request->query->getString('status', SubscriberStatus::SUBSCRIBED->value);
        $list_id = null;
        if ($request->query->has('list_id')) {
            $list_id = $request->query->getInt('list_id');
        }

        $subscribers = $this
            ->subscriberService
            ->getSubscribers($project, $status, $list_id, $limit, $offset)
            ->map(fn($subscriber) => new SubscriberObject($subscriber));

        return $this->json($subscribers);
    }

    #[Route('/subscribers', methods: 'POST')]
    public function createSubscriber(#[MapRequestPayload] CreateSubscriberInput $input, Project $project): JsonResponse
    {

        $missingListIds = $this
            ->newsletterListService
            ->isListsAvailable($project, $input->list_ids);

        if ($missingListIds !== null) {
            throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
        }

        $subscriberDB = $this->subscriberService->getSubscriberByEmail($project, $input->email);
        if ($subscriberDB !== null) {
            throw new UnprocessableEntityHttpException("Subscriber with email {$input->email} already exists");
        }

        $lists = $this->newsletterListService->getListsByIds($input->list_ids);

        $subscriber = $this->subscriberService->createSubscriber(
            $project,
            $input->email,
            $lists,
            $input->status ?? SubscriberStatus::SUBSCRIBED,
            $input->source ?? SubscriberSource::CONSOLE,
            $input->subscribe_ip,
            $input->subscribed_at ? \DateTimeImmutable::createFromTimestamp($input->subscribed_at) : null,
            $input->unsubscribed_at ? \DateTimeImmutable::createFromTimestamp($input->unsubscribed_at) : null,
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

        $updates = new UpdateSubscriberDto();

        if ($input->hasProperty('email')) {
            $subscriberDB = $this->subscriberService->getSubscriberByEmail($project, $input->email);
            if ($subscriberDB !== null) {
                throw new UnprocessableEntityHttpException("Subscriber with email {$input->email} already exists");
            }

            $updates->email = $input->email;
        }

        if ($input->hasProperty('list_ids')) {
            $missingListIds = $this->newsletterListService->isListsAvailable($project, $input->list_ids);

            if ($missingListIds !== null) {
                throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
            }

            $updates->lists = $this->newsletterListService->getListsByIds($input->list_ids);
        }

        if ($input->hasProperty('status')) {
            $updates->status = $input->status;
        }

        $subscriber = $this->subscriberService->updateSubscriber($subscriber, $updates);
        return $this->json(new SubscriberObject($subscriber));
    }

    #[Route('/subscribers/{id}', methods: 'DELETE')]
    public function deleteSubscriber(Subscriber $subscriber): JsonResponse
    {
        $this->subscriberService->deleteSubscriber($subscriber);
        return $this->json([]);
    }
}
