<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Subscriber\BulkActionSubscriberInput;
use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Input\Subscriber\UpdateSubscriberInput;
use App\Api\Console\Object\SubscriberObject;
use App\Entity\Newsletter;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\SubscriberService;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class SubscriberController extends AbstractController
{

    public function __construct(
        private SubscriberService         $subscriberService,
        private NewsletterListService     $newsletterListService,
        private SubscriberMetadataService $subscriberMetadataService
    )
    {
    }

    #[Route('/subscribers', methods: 'GET')]
    #[ScopeRequired(Scope::SUBSCRIBERS_READ)]
    public function getSubscribers(Request $request, Newsletter $newsletter): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $status = null;
        if ($request->query->has('status')) {
            $status = SubscriberStatus::tryFrom($request->query->getString('status'));
        }

        $listId = null;
        if ($request->query->has('listId')) {
            $listId = $request->query->getInt('listId');
        }

        $search = null;
        if ($request->query->has('search')) {
            $search = $request->query->getString('search');
        }

        $subscribers = $this
            ->subscriberService
            ->getSubscribers(
                $newsletter,
                $status,
                $listId,
                $search,
                $limit,
                $offset
            )
            ->map(fn($subscriber) => new SubscriberObject($subscriber));

        return $this->json($subscribers);
    }

    #[Route('/subscribers', methods: 'POST')]
    #[ScopeRequired(Scope::SUBSCRIBERS_WRITE)]
    public function createSubscriber(
        #[MapRequestPayload] CreateSubscriberInput $input,
        Newsletter                                 $newsletter
    ): JsonResponse
    {
        $missingListIds = $this
            ->newsletterListService
            ->getMissingListIdsOfNewsletter($newsletter, $input->list_ids);

        if ($missingListIds !== null) {
            throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
        }

        $subscriberDB = $this->subscriberService->getSubscriberByEmail($newsletter, $input->email);
        if ($subscriberDB !== null) {
            throw new UnprocessableEntityHttpException("Subscriber with email {$input->email} already exists");
        }

        $lists = $this->newsletterListService->getListsByIds($input->list_ids);

        $subscriber = $this->subscriberService->createSubscriber(
            $newsletter,
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
    #[ScopeRequired(Scope::SUBSCRIBERS_WRITE)]
    public function updateSubscriber(
        Subscriber                                 $subscriber,
        Newsletter                                 $newsletter,
        #[MapRequestPayload] UpdateSubscriberInput $input
    ): JsonResponse
    {
        $updates = new UpdateSubscriberDto();

        if ($input->hasProperty('email')) {
            $subscriberDB = $this->subscriberService->getSubscriberByEmail($newsletter, $input->email);
            if ($subscriberDB !== null) {
                throw new UnprocessableEntityHttpException("Subscriber with email {$input->email} already exists");
            }

            $updates->email = $input->email;
        }

        if ($input->hasProperty('list_ids')) {
            $missingListIds = $this->newsletterListService->getMissingListIdsOfNewsletter(
                $newsletter,
                $input->list_ids
            );

            if ($missingListIds !== null) {
                throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
            }

            $updates->lists = $this->newsletterListService->getListsByIds($input->list_ids);
        }

        if ($input->hasProperty('status')) {
            $updates->status = $input->status;
        }

        $metadataDefinitions = $this->subscriberMetadataService->getMetadataDefinitions($newsletter);

        if ($input->hasProperty('metadata')) {
            try {
                $this->subscriberMetadataService->validateMetadata($newsletter, $input->metadata);
            } catch (\Exception $e) {
                throw new UnprocessableEntityHttpException($e->getMessage());
            }
            $updates->metadata = $input->metadata;
        }

        $subscriber = $this->subscriberService->updateSubscriber($subscriber, $updates);
        return $this->json(new SubscriberObject($subscriber));
    }

    #[Route('/subscribers/{id}', methods: 'DELETE')]
    #[ScopeRequired(Scope::SUBSCRIBERS_WRITE)]
    public function deleteSubscriber(Subscriber $subscriber): JsonResponse
    {
        $this->subscriberService->deleteSubscriber($subscriber);
        return $this->json([]);
    }

    #[Route('/subscribers/bulk', methods: 'POST')]
    #[ScopeRequired(Scope::SUBSCRIBERS_WRITE)]
    public function bulkActions(Newsletter $newsletter, #[MapRequestPayload] BulkActionSubscriberInput $input): JsonResponse
    {
        if (count($input->subscribers_ids) >= $this->subscriberService::BULK_SUBSCRIBER_LIMIT) {
            throw new UnprocessableEntityHttpException("Subscribers limit exceeded");
        }

        $subscribers = [];
        $currentSubscribers = $this->subscriberService->getAllSubscribers($newsletter);
        // Validate that all subscriber IDs exist in the newsletter
        foreach ($input->subscribers_ids as $subscriberId) {
            $subscriber = array_find($currentSubscribers, fn($s) => $s->getId() === $subscriberId);
            if ($subscriber === null) {
                throw new UnprocessableEntityHttpException("Subscriber with ID {$subscriberId} not found in the newsletter");
            }
            $subscribers[] = $subscriber;
        }

        if ($input->action == 'delete') {
            $this->subscriberService->deleteSubscribers($subscribers);
            return $this->json(['status' => 'success', 'message' => 'Subscribers deleted successfully']);
        }

        if ($input->action == 'status_change') {
            if ($input->status == null)
                throw new UnprocessableEntityHttpException("Status must be provided for status change action");
            if (!SubscriberStatus::tryFrom($input->status)) {
                throw new UnprocessableEntityHttpException("Invalid status provided");
            }
            $status = SubscriberStatus::from($input->status);
            $this->subscriberService->updateSubscribersStatus($subscribers, $status);
            return $this->json(['status' => 'success', 'message' => 'Subscribers status updated successfully']);
        }

        if ($input->action == 'metadata_update') {
            if ($input->metadata == null)
                throw new UnprocessableEntityHttpException("Metadata must be provided for metadata update action");
            foreach ($subscribers as $subscriber) {
                $updates = new UpdateSubscriberDto();
                try {
                    $this->subscriberMetadataService->validateMetadata($newsletter, $input->metadata);
                } catch (\Exception $e) {
                    throw new UnprocessableEntityHttpException($e->getMessage());
                }
                $updates->metadata = $input->metadata;
                $this->subscriberService->updateSubscriber($subscriber, $updates);
            }
            return $this->json(['status' => 'success', 'message' => 'Subscribers metadata updated successfully']);
        }

        throw new BadRequestHttpException("Unhandled action");
    }
}
