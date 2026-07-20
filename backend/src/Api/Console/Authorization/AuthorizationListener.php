<?php

namespace App\Api\Console\Authorization;

use App\Entity\Newsletter;
use App\Service\ApiKey\ApiKeyService;
use App\Service\ApiKey\Dto\UpdateApiKeyDto;
use App\Service\Newsletter\NewsletterService;
use App\Service\User\UserService;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleApiAuthorizationListenerAbstract;

/**
 * @extends ConsoleApiAuthorizationListenerAbstract<Newsletter>
 */
#[AsEventListener(event: KernelEvents::CONTROLLER, priority: 200)]
class AuthorizationListener extends ConsoleApiAuthorizationListenerAbstract
{
    use ClockAwareTrait;

    public function __construct(
        private ApiKeyService $apiKeyService,
        private NewsletterService $newsletterService,
        private UserService $userService,
        InternalConfig $internalConfig,
        CloudApiService $cloudApiService,
        AuthInterface $auth,
    ) {
        parent::__construct(
            $internalConfig,
            $cloudApiService,
            $auth,
        );
    }

    protected function getBasePath(): string
    {
        return '/api/console';
    }

    protected function getBypassPaths(): array
    {
        return [
            '/api/console/init',
        ];
    }

    protected function isResourceApiKey(string $bearerToken): bool
    {
        return strlen($bearerToken) === ApiKeyService::API_KEY_LENGTH && ctype_xdigit($bearerToken);
    }

    protected function getResourceFromApiKey(string $apiKey): null|array
    {
        $apiKeyModel = $this->apiKeyService->getByRawKey($apiKey);

        if ($apiKeyModel === null) {
            return null;
        }

        return [
            'resource' => $apiKeyModel->getNewsletter(),
            'scopes' => $apiKeyModel->getScopes(),
            'apiKey' => $apiKeyModel,
        ];
    }

    protected function getResourceFromRequest(ControllerEvent $event): ?object
    {
        $newsletterId = $event->getRequest()->headers->get('x-newsletter-id');
        $newsletter = $this->newsletterService->getNewsletterById((int)$newsletterId);

        if ($newsletter === null) {
            return null;
        }

        return $newsletter;
    }

    protected function getResourceFromRequestError(): string
    {
        return 'Unable to find the newsletter from the request. Please provide a valid x-newsletter-id header.';
    }

    protected function getOrganizationIdFromResource(object $resource): int
    {
        return $resource->getOrganizationId();
    }

    protected function getUserResourceScopes(object $resource, int $userId): null|array
    {
        $user = $this->userService->getUser($resource, hyvorUserId: $userId);

        if ($user === null) {
            return null;
        }

        return array_map(fn($scope) => $scope->value, $user->getRole()->scopes());
    }

    protected function onProductApiKeyUse(object $apiKeyModel): void
    {
        $apiKeyUpdates = new UpdateApiKeyDto();
        $apiKeyUpdates->lastAccessedAt = $this->now();
        $this->apiKeyService->updateApiKey($apiKeyModel, $apiKeyUpdates);
    }

}
