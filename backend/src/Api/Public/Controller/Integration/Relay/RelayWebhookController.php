<?php

namespace App\Api\Public\Controller\Integration\Relay;

use App\Entity\Type\RelayDomainStatus;
use App\Service\Domain\DomainService;
use App\Service\Domain\Dto\UpdateDomainDto;
use App\Service\Issue\Dto\UpdateSendDto;
use App\Service\Issue\SendService;
use App\Service\Subscriber\SubscriberService;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class RelayWebhookController extends AbstractController
{
    use ClockAwareTrait;

    public function __construct(
        private DomainService     $domainService,
        private SubscriberService $subscriberService,
        private SendService       $sendService,
    )
    {
    }

    #[Route('/integration/relay/webhook', methods: 'POST')]
    public function handleWebhook(Request $request): JsonResponse
    {
        $content = $request->getContent();
        /** @var array{
         *     'event': string,
         *     'payload': array<string, mixed>
         *  } $data
         */
        $data = json_decode($content, true);

        // TODO: Validate the webhook

        $event = $data['event'];
        $payload = $data['payload'];

        /** **************** EVENTS **************** */
        if (str_starts_with('send.recipient.', $event)) {
            /** @var array{
             *     'send': array{
             *         'headers': array<string, string>
             *     },
             *     'attempt': array{
             *         'created_at': string
             *     }
             * } $payload
             */
            $this->handleSendRecipientWebhooks($payload, $event);
        }

        if ($event === 'domain.status.changed') {
            /** @var array{
             *     domain: array{
             *         domain: string
             *    },
             *    new_status: string
             * } $payload
             */
            $this->handleDomainStatusChanged($payload);
        }

        if ($event === 'suppression.created') {
            /** @var array{
             *     suppression: array{
             *          email: string,
             *          reason: string,
             *          description?: string
             *      }
             *  } $payload
             */
            $this->handleSuppressionCreated($payload);
        }

        return new JsonResponse();
    }

    /**
     * @param array{
     *     'send': array{
     *         'headers': array<string, string>
     *     },
     *     'attempt': array{
     *         'created_at': string
     *     }
     * } $payload
     */
    private function handleSendRecipientWebhooks(array $payload, string $event): void
    {
        $send = $payload['send'];
        $attempt = $payload['attempt'];

        $sendId = $send['headers']['X-Newsletter-Send-ID'];
        $send = $this->sendService->getSendById((int)$sendId);

        if ($send === null) {
            throw new BadRequestHttpException('Send not found');
        }

        $updates = new UpdateSendDto();
        $attemptedTime = \DateTimeImmutable::createFromTimestamp((int)$attempt['created_at']);

        if ($event === 'send.recipient.accepted') {
            $updates->deliveredAt = $attemptedTime;
        }
        if ($event === 'send.recipient.failed') {
            $updates->failedAt = $attemptedTime;
        }
        if ($event === 'send.recipient.bounced') {
            $updates->bouncedAt = $attemptedTime;
        }
        if ($event === 'send.recipient.complained') {
            $updates->complainedAt = $attemptedTime;
        }

        $this->sendService->updateSend($send, $updates);
    }

    /**
     * @param array{
     *     'domain': array{
     *         'domain': string
     *    },
     *     'new_status': string
     * } $payload
     */
    private function handleDomainStatusChanged(array $payload): void
    {
        $domainName = $payload['domain']['domain'];
        $domain = $this->domainService->getDomainByDomainName($domainName);

        if ($domain === null) {
            throw new BadRequestHttpException('Domain not found');
        }

        $newStatus = RelayDomainStatus::from($payload['new_status']);

        $updates = new UpdateDomainDto();
        $updates->relayStatus = $newStatus;

        $this->domainService->updateDomain($domain, $updates);
    }

    /**
     * @param array{
     *     'suppression': array{
     *          'email': string,
     *          'reason': string,
     *          'description'?: string
     *     }
     * } $payload
     */
    private function handleSuppressionCreated(array $payload): void
    {
        $suppression = $payload['suppression'];
        $suppressedEmail = $suppression['email'];
        $reason = $suppression['reason'];
        $description = $suppression['description'] ?? null;

        $this->subscriberService->unsubscribeByEmail(
            $suppressedEmail,
            reason: "$reason" . ($description ? " - $description" : '')
        );
    }
}
