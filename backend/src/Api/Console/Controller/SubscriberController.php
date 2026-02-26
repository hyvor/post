<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Subscriber\AddSubscriberListInput;
use App\Api\Console\Input\Subscriber\BulkActionSubscriberInput;
use App\Api\Console\Input\Subscriber\CreateSubscriberIfExists;
use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Input\Subscriber\RemoveSubscriberListInput;
use App\Api\Console\Input\Subscriber\RemoveSubscriberListReason;
use App\Api\Console\Input\Subscriber\SubscriberListIfUnsubscribed;
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
        private SubscriberMetadataService $subscriberMetadataService,
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
        if ($request->query->has('list_id')) {
            $listId = $request->query->getInt('list_id');
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

        $subscriber = $this->subscriberService->getSubscriberByEmail($newsletter, $input->email);

        if ($subscriber === null) {

            // create subscriber
            $subscriber = $this->subscriberService->createSubscriber(
                $newsletter,
                $input->email,
                [],
                SubscriberStatus::PENDING,
                source: $input->source ?? SubscriberSource::CONSOLE,
                subscribeIp: $input->subscribe_ip ?? null,
                subscribedAt: $input->has('subscribed_at') ? \DateTimeImmutable::createFromTimestamp($input->subscribed_at) : null,
                unsubscribedAt: $input->has('unsubscribed_at') ? \DateTimeImmutable::createFromTimestamp($input->unsubscribed_at) : null,
            );

        } elseif ($input->if_exists === CreateSubscriberIfExists::UPDATE) {

            // update
            $updates = new UpdateSubscriberDto();

            if ($updates->has('status')) {
                $updates->status = $input->status;
            }

            if ($updates->has('subscribe_ip')) {
                $updates->subscribeIp = $input->subscribe_ip;
            }

            if ($updates->has('subscribed_at')) {
                $updates->subscribedAt = $input->subscribed_at ? \DateTimeImmutable::createFromTimestamp($input->subscribed_at) : null;
            }

            if ($updates->has('unsubscribed_at')) {
                $updates->unsubscribedAt = $input->unsubscribed_at ? \DateTimeImmutable::createFromTimestamp($input->unsubscribed_at) : null;
            }

            $subscriber = $this->subscriberService->updateSubscriber($subscriber, $updates);

        } else {
            throw new UnprocessableEntityHttpException("Subscriber with email {$input->email} already exists");
        }

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

        if ($input->has('email')) {
            $subscriberDB = $this->subscriberService->getSubscriberByEmail($newsletter, $input->email);
            if ($subscriberDB !== null) {
                throw new UnprocessableEntityHttpException("Subscriber with email {$input->email} already exists");
            }

            $updates->email = $input->email;
        }

        if ($input->has('status')) {
            if ($input->status === SubscriberStatus::SUBSCRIBED && $subscriber->getOptInAt() === null) {
                throw new UnprocessableEntityHttpException('Subscribers without opt-in can not be updated to SUBSCRIBED status.');
            }

            $updates->status = $input->status;
        }

        $metadataDefinitions = $this->subscriberMetadataService->getMetadataDefinitions($newsletter);

        if ($input->has('metadata')) {
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
            return $this->json([
                'status' => 'success',
                'message' => 'Subscribers deleted successfully',
                'subscribers' => []
            ]);
        }

        if ($input->action == 'status_change') {
            if ($input->status == null)
                throw new UnprocessableEntityHttpException("Status must be provided for status change action");

            $status = SubscriberStatus::tryFrom($input->status);
            if (!$status) {
                throw new UnprocessableEntityHttpException("Invalid status provided");
            }

            foreach ($subscribers as $subscriber) {
                $updates = new UpdateSubscriberDto();

                if ($status === SubscriberStatus::SUBSCRIBED && $subscriber->getOptInAt() === null) {
                    $updates->status = SubscriberStatus::PENDING;
                } else {
                    $updates->status = $status;
                }

                $this->subscriberService->updateSubscriber($subscriber, $updates);
            }

            return $this->json([
                'status' => 'success',
                'message' => 'Subscribers status updated successfully',
                'subscribers' => array_map(fn($s) => new SubscriberObject($s), $subscribers)
            ]);
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

            return $this->json([
                'status' => 'success',
                'message' => 'Subscribers metadata updated successfully',
                'subscribers' => array_map(fn($s) => new SubscriberObject($s), $subscribers)
            ]);
        }

        throw new BadRequestHttpException("Unhandled action");
    }

    #[Route('/subscribers/{id}/lists', methods: 'POST')]
    #[ScopeRequired(Scope::SUBSCRIBERS_WRITE)]
    public function addSubscriberList(
        Subscriber                                  $subscriber,
        Newsletter                                  $newsletter,
        #[MapRequestPayload] AddSubscriberListInput $input
    ): JsonResponse
    {
        if ($input->id === null && $input->name === null) {
            throw new UnprocessableEntityHttpException('Either id or name must be provided');
        }

        $list = $this->newsletterListService->getListByIdOrName($newsletter, $input->id, $input->name);

        if ($list === null) {
            throw new UnprocessableEntityHttpException('List not found');
        }

        try {
            $this->subscriberService->addSubscriberToList(
                $subscriber,
                $list,
                $input->if_unsubscribed === SubscriberListIfUnsubscribed::ERROR
            );
        } catch (\RuntimeException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return $this->json(new SubscriberObject($subscriber));
    }

    #[Route('/subscribers/{id}/lists', methods: 'DELETE')]
    #[ScopeRequired(Scope::SUBSCRIBERS_WRITE)]
    public function removeSubscriberList(
        Subscriber                                    $subscriber,
        Newsletter                                    $newsletter,
        #[MapRequestPayload] RemoveSubscriberListInput $input
    ): JsonResponse
    {
        if ($input->id === null && $input->name === null) {
            throw new UnprocessableEntityHttpException('Either id or name must be provided');
        }

        $list = $this->newsletterListService->getListByIdOrName($newsletter, $input->id, $input->name);

        if ($list === null) {
            throw new UnprocessableEntityHttpException('List not found');
        }

        $this->subscriberService->removeSubscriberFromList(
            $subscriber,
            $list,
            $input->reason === RemoveSubscriberListReason::UNSUBSCRIBE
        );

        return $this->json(new SubscriberObject($subscriber));
    }
}
