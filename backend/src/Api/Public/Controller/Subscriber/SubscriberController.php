<?php

namespace App\Api\Public\Controller\Subscriber;

use App\Api\Public\Input\Subscriber\ResubscribeInput;
use App\Api\Public\Input\Subscriber\UnsubscribeInput;
use App\Api\Public\Object\Form\FormListObject;
use App\Entity\Type\SubscriberStatus;
use App\Service\Issue\SendService;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\SubscriberService;
use Hyvor\Internal\Util\Crypt\Encryption;
use Illuminate\Contracts\Encryption\DecryptException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class SubscriberController extends AbstractController
{
    use ClockAwareTrait;

    public function __construct(
        private SubscriberService     $subscriberService,
        private SendService           $sendService,
        private NewsletterListService $newsletterListService,
        private Encryption            $encryption,
    )
    {
    }

    #[Route('/subscriber/confirm', methods: ['GET'])]
    public function confirm(Request $request): JsonResponse
    {
        $token = $request->query->getString('token');

        try {
            $data = $this->encryption->decrypt($token);
        } catch (DecryptException) {
            throw new BadRequestHttpException('Invalid confirmation token.');
        }

        if (!$data || !is_array($data) || !isset($data['subscriber_id'], $data['expires_at'])) {
            throw new BadRequestHttpException('Invalid confirmation token.');
        }

        $subscriber = $this->subscriberService->getSubscriberById($data['subscriber_id']);
        if (!$subscriber) {
            throw new BadRequestHttpException('Subscriber not found.');
        }

        assert(is_string($data['expires_at']));
        if (new \DateTimeImmutable($data['expires_at'])->getTimestamp() < $this->now()->getTimestamp()) {
            throw new BadRequestHttpException(
                'The confirmation link has expired. Please request a new confirmation link.'
            );
        }

        $updates = new UpdateSubscriberDto();
        $updates->status = SubscriberStatus::SUBSCRIBED;
        $updates->subscribedAt = $this->now();

        $this->subscriberService->updateSubscriber($subscriber, $updates);

        return new JsonResponse();
    }

    #[Route('/subscriber/unsubscribe', methods: ['POST'])]
    public function unsubscribe(
        #[MapRequestPayload] UnsubscribeInput $input
    ): JsonResponse
    {
        try {
            $sendId = $this->encryption->decrypt($input->token);
        } catch (DecryptException) {
            throw new BadRequestHttpException('Invalid unsubscribe token.');
        }

        if (!$sendId || !is_int($sendId)) {
            throw new BadRequestHttpException('Invalid unsubscribe token.');
        }

        $send = $this->sendService->getSendById($sendId);

        if (!$send) {
            throw new BadRequestHttpException('Newsletter send not found.');
        }

        $this->subscriberService->unsubscribeBySend($send);

        $lists = $this->newsletterListService->getListsOfNewsletter($send->getNewsletter());

        return new JsonResponse([
            'lists' => $lists->map(fn($list) => new FormListObject($list))->toArray(),
        ]);
    }

    #[Route('/subscriber/resubscribe', methods: ['PATCH'])]
    public function resubscribe(
        #[MapRequestPayload] ResubscribeInput $input,
    ): JsonResponse
    {
        try {
            $sendId = $this->encryption->decrypt($input->token);
        } catch (DecryptException) {
            throw new BadRequestHttpException('Invalid unsubscribe token.');
        }

        if (!$sendId || !is_int($sendId)) {
            throw new BadRequestHttpException('Invalid unsubscribe token.');
        }

        $send = $this->sendService->getSendById($sendId);

        if (!$send) {
            throw new BadRequestHttpException('Newsletter send not found.');
        }

        $subscriber = $send->getSubscriber();

        $updates = new UpdateSubscriberDto();

        $missingListIds = $this->newsletterListService->getMissingListIdsOfNewsletter(
            $send->getNewsletter(),
            $input->list_ids
        );

        if ($missingListIds !== null) {
            throw new UnprocessableEntityHttpException("List with id {$missingListIds[0]} not found");
        }

        $updates->lists = $this->newsletterListService->getListsByIds($input->list_ids);
        $this->subscriberService->updateSubscriber($subscriber, $updates);

        return new JsonResponse();
    }
}
