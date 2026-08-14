<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Input\Subscriber\BulkActionSubscriberInput;
use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Input\Subscriber\ListsStrategy;
use App\Api\Console\Input\Subscriber\MetadataStrategy;
use App\Api\Console\Object\SubscriberObject;
use App\Entity\Newsletter;
use App\Entity\NewsletterList;
use App\Entity\Subscriber;
use App\Entity\Type\ListRemovalReason;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\ConfirmationMail\SendConfirmationMailMessage;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\ListRemoval\ListRemovalService;
use App\Service\Subscriber\SubscriberService;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Service\SubscriberMetadata\Exception\MetadataValidationFailedException;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class SubscribersController extends AbstractController
{

    public function __construct(
        private SubscriberService $subscriberService,
        private NewsletterListService $newsletterListService,
        private SubscriberMetadataService $subscriberMetadataService,
        private ListRemovalService $listRemovalService,
        private MessageBusInterface $bus,
    ) {}

    #[Route('/subscribers', methods: 'GET')]
    #[ScopeRequired(PostScope::SUBSCRIBERS_READ)]
    #[OA\Get(
        description: 'Get subscribers of the newsletter, paginated and optionally filtered by status, list, or search term.',
        summary: 'Get subscribers',
    )]
    #[OA\Response(
        response: 200,
        description: 'List of subscribers',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SubscriberObject::class)),
        ),
    )]
    public function list(Request $request, Newsletter $newsletter): JsonResponse
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
    #[ScopeRequired(PostScope::SUBSCRIBERS_WRITE)]
    #[OA\Post(
        description: 'Creates a new subscriber, or updates the existing subscriber if one already exists with the given email.',
        summary: 'Create or update a subscriber',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the created or updated subscriber object.',
        content: new Model(type: SubscriberObject::class),
    )]
    public function create(
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
                    // remove
                    $newLists = array_filter(
                        $newLists,
                        fn($l) => !array_find($resolvedLists, fn($rl) => $rl->getId() === $l->getId()),
                    );
                }

                $newLists = $this->skipLists(
                    $subscriber,
                    $newLists,
                    $input->getListSkipResubscribeOn(),
                );

                $updates->lists = $newLists;
            }

            $subscriber = $this->subscriberService->updateSubscriber(
                $subscriber,
                $updates,
                listRemovalReason: $input->list_removal_reason,
                sendConfirmationEmail: $input->send_pending_confirmation_email,
            );
        }

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

    /**
     * @param Subscriber $subscriber
     * @param NewsletterList[] $lists
     * @param ListRemovalReason[] $reasonsToSkip
     * @return NewsletterList[]
     */
    private function skipLists(Subscriber $subscriber, array $lists, array $reasonsToSkip): array
    {
        $newlyAddedLists = [];

        foreach ($lists as $list) {
            if (!$subscriber->getLists()->contains($list)) {
                $newlyAddedLists[] = $list;
            }
        }

        if (count($newlyAddedLists) === 0) {
            return $lists;
        }

        $newlyAddedListIds = array_map(fn($l) => $l->getId(), $newlyAddedLists);

        $removals = $this->listRemovalService->getRemovals($subscriber, $newlyAddedListIds, $reasonsToSkip);

        return array_filter(
            $lists,
            fn($list) => !array_find($removals, fn($r) => $r->getList()->getId() === $list->getId()),
        );
    }

    #[Route('/subscribers/email/{email}', methods: 'GET')]
    #[ScopeRequired(PostScope::SUBSCRIBERS_READ)]
    #[OA\Get(
        description: 'Get a subscriber of the newsletter by email address.',
        summary: 'Get a subscriber by email',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the subscriber object.',
        content: new Model(type: SubscriberObject::class),
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when no subscriber exists with the given email.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Subscriber not found'),
            ],
        ),
    )]
    public function getByEmail(string $email, Newsletter $newsletter): JsonResponse
    {
        $subscriber = $this->subscriberService->getSubscriberByEmail($newsletter, $email);

        if ($subscriber === null) {
            return $this->json(['message' => 'Subscriber not found'], 404);
        }

        return $this->json(new SubscriberObject($subscriber));
    }

    #[Route('/subscribers/{id}/resend-opt-in', methods: 'POST')]
    #[ScopeRequired(PostScope::SUBSCRIBERS_WRITE)]
    #[OA\Post(
        description: 'Resends the opt-in confirmation email to a pending subscriber.',
        summary: 'Resend opt-in confirmation email',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns an empty object on success.',
        content: new OA\JsonContent(),
    )]
    public function resendOptIn(Subscriber $subscriber): JsonResponse
    {
        if ($subscriber->getStatus() !== SubscriberStatus::PENDING) {
            throw new BadRequestHttpException("Subscriber is not pending");
        }
        $this->bus->dispatch(new SendConfirmationMailMessage($subscriber->getId()));
        return $this->json([]);
    }

    #[Route('/subscribers/{id}', methods: 'DELETE')]
    #[ScopeRequired(PostScope::SUBSCRIBERS_WRITE)]
    #[OA\Delete(
        description: 'Deletes a subscriber.',
        summary: 'Delete a subscriber',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns an empty object on success.',
        content: new OA\JsonContent(),
    )]
    public function delete(Subscriber $subscriber): JsonResponse
    {
        $this->subscriberService->deleteSubscriber($subscriber);
        return $this->json([]);
    }

    #[Route('/subscribers/bulk', methods: 'POST')]
    #[ScopeRequired(PostScope::SUBSCRIBERS_WRITE)]
    #[OA\Post(
        description: 'Performs a bulk action (delete, status change, or metadata update) on a set of subscribers.',
        summary: 'Bulk update subscribers',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the result of the bulk action.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(
                    property: 'subscribers',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: SubscriberObject::class)),
                ),
            ],
        ),
    )]
    public function bulk(
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
                $updates->status = $status;
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
