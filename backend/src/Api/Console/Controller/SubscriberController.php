<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Subscriber\BulkActionSubscriberInput;
use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Input\Subscriber\ListsStrategy;
use App\Api\Console\Input\Subscriber\MetadataStrategy;
use App\Api\Console\Object\SubscriberObject;
use App\Entity\Newsletter;
use App\Entity\NewsletterList;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\SubscriberService;
use App\Service\SubscriberMetadata\Exception\MetadataValidationFailedException;
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
        private SubscriberService $subscriberService,
        private NewsletterListService $newsletterListService,
        private SubscriberMetadataService $subscriberMetadataService,
    ) {}

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
                $offset,
            )
            ->map(fn($subscriber) => new SubscriberObject($subscriber));

        return $this->json($subscribers);
    }

    #[Route('/subscribers', methods: 'POST')]
    #[ScopeRequired(Scope::SUBSCRIBERS_WRITE)]
    public function createSubscriber(
        #[MapRequestPayload] CreateSubscriberInput $input,
        Newsletter $newsletter,
    ): JsonResponse {
        $resolvedLists = $input->lists ? $this->resolveLists($newsletter, $input->lists) : [];
        $subscriber = $this->subscriberService->getSubscriberByEmail($newsletter, $input->email);

        if ($input->metadata) {
            try {
                $this->subscriberMetadataService->validateMetadata(
                    $newsletter,
                    $input->metadata,
                );
            } catch (MetadataValidationFailedException $e) {
                throw new UnprocessableEntityHttpException($e->getMessage());
            }
        }

        if ($subscriber === null) {
            $subscriber = $this->subscriberService->createSubscriber(
                $newsletter,
                $input->email,
                $resolvedLists,
                status: $input->status ?? SubscriberStatus::SUBSCRIBED,
                source: $input->source ?? SubscriberSource::CONSOLE,
                subscribeIp: $input->getSubscribeIp(),
                subscribedAt: $input->getSubscribedAt(),
                metadata: $input->metadata ?? [],
                sendConfirmationEmail: $input->send_pending_confirmation_email,
            );
        } else {
            $updates = new UpdateSubscriberDto();

            if ($input->status) {
                $updates->status = $input->status;
            }

            if ($input->source) {
                $updates->source = $input->source;
            }

            if ($input->has('subscribe_ip')) {
                $updates->subscribeIp = $input->subscribe_ip;
            }

            if ($input->has('subscribed_at')) {
                $updates->subscribedAt = $input->subscribed_at !== null
                    ? \DateTimeImmutable::createFromTimestamp($input->subscribed_at)
                    : null;
            }

            if ($input->metadata) {
                if ($input->metadata_strategy === MetadataStrategy::MERGE) {
                    $updates->metadata = array_merge(
                        $subscriber->getMetadata(),
                        $input->metadata,
                    );
                } else {
                    $updates->metadata = $input->metadata;
                }
            }

            if ($input->lists !== null) {
                $newLists = $subscriber->getLists()->toArray();

                if ($input->lists_strategy === ListsStrategy::MERGE) {
                    foreach ($resolvedLists as $list) {
                        if (!array_find($newLists, fn($l) => $l->getId() === $list->getId())) {
                            $newLists[] = $list;
                        }
                    }
                } elseif ($input->lists_strategy === ListsStrategy::OVERWRITE) {
                    $newLists = $resolvedLists;
                } else {
                    $newLists = array_filter(
                        $newLists,
                        fn($l) => !array_find($resolvedLists, fn($rl) => $rl->getId() === $l->getId()),
                    );
                }

                $updates->lists = $newLists;
            }

            $subscriber = $this->subscriberService->updateSubscriber($subscriber, $updates);
        }

//        // Sync lists
//        $resolvedListIds = array_map(fn($l) => $l->getId(), $resolvedLists);
//        $currentListIds = $subscriber->getLists()->map(fn($l) => $l->getId())->toArray();
//
//        // Add new lists
//        foreach ($resolvedLists as $list) {
//            if (!in_array($list->getId(), $currentListIds)) {
//                if (
//                    $input->list_add_strategy_if_unsubscribed === ListAddStrategyIfUnsubscribed::IGNORE &&
//                    $this->subscriberService->hasSubscriberUnsubscribedFromList($subscriber, $list)
//                ) {
//                    continue;
//                }
//                $this->subscriberService->addSubscriberToList($subscriber, $list);
//            }
//        }
//
//        // Remove lists no longer in the resolved set
//        foreach ($subscriber->getLists()->toArray() as $existingList) {
//            if (!in_array($existingList->getId(), $resolvedListIds)) {
//                $this->subscriberService->removeSubscriberFromList(
//                    $subscriber,
//                    $existingList,
//                    $input->list_remove_reason === ListRemoveReason::UNSUBSCRIBE,
//                );
//            }
//        }

        return $this->json(new SubscriberObject($subscriber));
    }

    /**
     * @param (string|int)[] $listIdsOrNames
     * @return NewsletterList[]
     */
    private function resolveLists(Newsletter $newsletter, array $listIdsOrNames): array
    {
        $listIds = [];
        $listNames = [];

        foreach ($listIdsOrNames as $listIdOrName) {
            if (is_int($listIdOrName)) {
                $listIds[] = $listIdOrName;
            } elseif (is_string($listIdOrName)) {
                $listNames[] = $listIdOrName;
            }
        }

        $resolvedLists = [];

        if (count($listIds) > 0) {
            $resolvedLists = $this->newsletterListService->getListsByIds($newsletter, $listIds);

            if (count($resolvedLists) !== count($listIds)) {
                $resolvedListIds = array_map(fn($l) => $l->getId(), $resolvedLists);
                $missingIds = array_diff($listIds, $resolvedListIds);
                throw new UnprocessableEntityHttpException(
                    "Lists with IDs " . implode(', ', $missingIds) . " not found",
                );
            }
        }

        if (count($listNames) > 0) {
            $listsByName = $this->newsletterListService->getListsByNames($newsletter, $listNames);

            foreach ($listsByName as $list) {
                if (!in_array($list, $resolvedLists)) {
                    $resolvedLists[] = $list;
                }
            }

            if (count($listsByName) !== count($listNames)) {
                $resolvedListNames = array_map(fn($l) => $l->getName(), $listsByName);
                $missingNames = array_diff($listNames, $resolvedListNames);
                throw new UnprocessableEntityHttpException(
                    "Lists with names " . implode(', ', $missingNames) . " not found",
                );
            }
        }

        return $resolvedLists;
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
    public function bulkActions(
        Newsletter $newsletter,
        #[MapRequestPayload] BulkActionSubscriberInput $input,
    ): JsonResponse {
        if (count($input->subscribers_ids) >= $this->subscriberService::BULK_SUBSCRIBER_LIMIT) {
            throw new UnprocessableEntityHttpException("Subscribers limit exceeded");
        }

        $subscribers = [];
        $currentSubscribers = $this->subscriberService->getAllSubscribers($newsletter);
        // Validate that all subscriber IDs exist in the newsletter
        foreach ($input->subscribers_ids as $subscriberId) {
            $subscriber = array_find($currentSubscribers, fn($s) => $s->getId() === $subscriberId);

            if ($subscriber === null) {
                throw new UnprocessableEntityHttpException(
                    "Subscriber with ID {$subscriberId} not found in the newsletter",
                );
            }

            $subscribers[] = $subscriber;
        }

        if ($input->action == 'delete') {
            $this->subscriberService->deleteSubscribers($subscribers);
            return $this->json([
                'status' => 'success',
                'message' => 'Subscribers deleted successfully',
                'subscribers' => [],
            ]);
        }

        if ($input->action == 'status_change') {
            if ($input->status == null) {
                throw new UnprocessableEntityHttpException("Status must be provided for status change action");
            }

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
                'subscribers' => array_map(fn($s) => new SubscriberObject($s), $subscribers),
            ]);
        }

        if ($input->action == 'metadata_update') {
            if ($input->metadata == null) {
                throw new UnprocessableEntityHttpException("Metadata must be provided for metadata update action");
            }

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
                'subscribers' => array_map(fn($s) => new SubscriberObject($s), $subscribers),
            ]);
        }

        throw new BadRequestHttpException("Unhandled action");
    }
}
