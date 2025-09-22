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
        /** @var array<string, string|mixed> $data */
        $data = json_decode($content, true);

        // TODO: Validate the webhook

        assert(isset($data['event']) && is_string($data['event']));
        $event = $data['event'];

        assert(
            isset($data['payload'])
            && is_array($data['payload'])
        );

        /** @var array<string, mixed> $payload */
        $payload = $data['payload'];


        /** **************** EVENTS **************** */
        if (str_starts_with('send.recipient.', $event)) {
            $this->handleSendRecipientWebhooks($payload, $event);
        }

        if ($event === 'domain.status.changed') {
            $this->handleDomainStatusChanged($payload);
        }

        if ($event === 'suppression.created') {
            $this->handleSuppressionCreated($payload);
        }

        return new JsonResponse();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleSendRecipientWebhooks(array $payload, string $event): void
    {
        assert(
            isset($payload['send'])
            && is_array($payload['send'])
            && isset($payload['attempt'])
            && is_array($payload['attempt'])
        );
        /** @var array<string, mixed> $send */
        $send = $payload['send'];
        /** @var array<string, mixed> $attempt */
        $attempt = $payload['attempt'];

        assert(is_array($send['headers']));
        /** @var string $sendId */
        $sendId = $send['headers']['X-Newsletter-Send-ID'];

        $send = $this->sendService->getSendById((int)$sendId);

        if ($send === null) {
            throw new BadRequestHttpException('Send not found');
        }
        $updates = new UpdateSendDto();

        assert(isset($attempt['created_at']) && is_int($attempt['created_at']));
        $attemptedTime = \DateTimeImmutable::createFromTimestamp($attempt['created_at']);

        if ($event === 'send.recipient.accepted') {
            $updates->deliveredAt = $attemptedTime;
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
     * @param array<string, mixed> $payload
     */
    public function handleSendRecipientDeferred(array $payload): void
    {

    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleDomainStatusChanged(array $payload): void
    {
        assert(
            isset($payload['domain'])
            && is_array($payload['domain'])
        );

        /** @var string $domainName */
        $domainName = $payload['domain']['domain'];
        $domain = $this->domainService->getDomainByDomainName($domainName);

        if ($domain === null) {
            throw new BadRequestHttpException('Domain not found');
        }

        assert(
            isset($payload['new_status'])
            && is_string($payload['new_status'])
        );
        $newStatus = RelayDomainStatus::from($payload['new_status']);

        $updates = new UpdateDomainDto();
        $updates->relayStatus = $newStatus;

        $this->domainService->updateDomain($domain, $updates);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleSuppressionCreated(array $payload): void
    {
        assert(
            isset($payload['suppression'])
            && is_array($payload['suppression'])
        );

        $suppression = $payload['suppression'];
        /** @var string $suppressedEmail */
        $suppressedEmail = $suppression['email'];
        /** @var string $reason */
        $reason = $suppression['reason'];
        /** @var string|null $description */
        $description = $suppression['description'] ?? null;

        $this->subscriberService->unsubscribeByEmail(
            $suppressedEmail,
            reason: "$reason" . ($description ? " - $description" : '')
        );
    }
}
