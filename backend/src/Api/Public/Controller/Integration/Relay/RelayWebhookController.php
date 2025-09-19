<?php

namespace App\Api\Public\Controller\Integration\Relay;

use App\Entity\Type\RelayDomainStatus;
use App\Service\Domain\DomainService;
use App\Service\Domain\Dto\UpdateDomainDto;
use App\Service\Subscriber\SubscriberService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $payload = $data['payload'];

        if ($event === 'domain.status.changed') {

            assert(
                isset($payload['domain'])
                && is_array($payload['domain'])
            );

            /** @var string $domainName */
            $domainName = $payload['domain']['domain'];
            $domain = $this->domainService->getDomainByDomainName($domainName);

            if ($domain === null) {
                throw new \HttpException('Domain not found');
            }

            $newStatus = $payload['new_status'];
            $updates = new UpdateDomainDto();
            $isDomainActive = $domain->getRelayStatus() === RelayDomainStatus::ACTIVE;

            if (!$isDomainActive && $newStatus === 'active') {
                $updates->verifiedInRelay = true;
                $updates->relayStatus = RelayDomainStatus::ACTIVE;
            }

            if ($isDomainActive && $newStatus === 'warning') {
                $updates->relayStatus = RelayDomainStatus::WARNING;
            }

            if ($isDomainActive && $newStatus === 'suspended') {
                $updates->verifiedInRelay = false;
                $updates->relayStatus = RelayDomainStatus::SUSPENDED;
            }

            $this->domainService->updateDomain($domain, $updates);
        }

        if ($event === 'suppression.created') {

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

        return new JsonResponse();
    }
}
