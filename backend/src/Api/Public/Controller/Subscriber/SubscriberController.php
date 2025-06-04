<?php

namespace App\Api\Public\Controller\Subscriber;

use App\Entity\Type\SubscriberStatus;
use App\Service\Subscriber\Dto\UpdateSubscriberDto;
use App\Service\Subscriber\SubscriberService;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class SubscriberController extends AbstractController
{
    use ClockAwareTrait;
    public function __construct(
        private SubscriberService $subscriberService,
        private Encryption $encryption,
    ) {
    }

    #[Route('/subscriber/confirm', methods: ['GET'])]
    public function confirm(Request $request): RedirectResponse
    {
        $token = $request->query->getString('token');

        $data = $this->encryption->decrypt($token);

        if (!$data || !is_array($data) || !isset($data['subscriber_id'], $data['expires_at'])) {
            throw new BadRequestHttpException('Invalid confirmation token.');
        }

        $subscriber = $this->subscriberService->getSubscriberById($data['subscriber_id']);
        if (!$subscriber) {
            throw new BadRequestHttpException('Invalid subscriber ID.');
        }

        assert(is_string($data['expires_at']));
        if (new \DateTimeImmutable($data['expires_at'])->getTimestamp() < $this->now()->getTimestamp()) {
            throw new BadRequestHttpException(
                'The confirmation link has expired. Please request a new confirmation link.'
            );
        }

        $updates = new UpdateSubscriberDto();
        $updates->status = SubscriberStatus::SUBSCRIBED;

        $this->subscriberService->updateSubscriber($subscriber, $updates);

        return $this->redirect('https://post.hyvor.dev/newsletter/' . $subscriber->getNewsletter()->getSlug() . '/confirm?token=' . $token);
    }
}
