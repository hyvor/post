<?php

namespace App\Api\Public\Controller\Integration\Relay;

use App\Entity\Type\RelayDomainStatus;
use App\Service\Domain\DomainService;
use App\Service\Domain\Dto\UpdateDomainDto;
use App\Service\Subscriber\SubscriberService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class RelayWebhookController extends AbstractController
{
    public function __construct(
        private DomainService     $domainService,
        private SubscriberService $subscriberService,
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

        assert(isset($data['event']));
        $event = $data['event'];

        assert(
            isset($data['payload'])
            && is_array($data['payload'])
        );

        /** @var array<string, mixed> $payload */
        $payload = $data['payload'];


        /** **************** EVENTS **************** */
        if ($event === 'send.recipient.accepted') {
            $this->handleSendRecipientAccepted($payload);
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
    private function handleSendRecipientAccepted(array $payload): void
    {
        assert(isset($payload['recipient']));
        // TODO
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
