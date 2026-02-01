<?php

namespace App\Api\Console\Authorization;

use App\Entity\ApiKey;
use App\Entity\Newsletter;
use App\Service\ApiKey\ApiKeyService;
use App\Service\ApiKey\Dto\UpdateApiKeyDto;
use App\Service\Newsletter\NewsletterService;
use App\Service\User\UserService;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER, priority: 200)]
class AuthorizationListener
{
    use ClockAwareTrait;

    public const string RESOLVED_NEWSLETTER_ATTRIBUTE_KEY = 'console_api_resolved_newsletter';
    public const string RESOLVED_API_KEY_ATTRIBUTE_KEY = 'console_api_resolved_api_key';
    public const string RESOLVED_USER_ATTRIBUTE_KEY = 'console_api_resolved_user';
    public const string RESOLVED_ORGANIZATION_ATTRIBUTE_KEY = 'console_api_resolved_organization';

    public function __construct(
        private AuthInterface     $auth,
        private ApiKeyService     $apiKeyService,
        private NewsletterService $newsletterService,
        private UserService       $userService
    )
    {
    }

    public function __invoke(ControllerEvent $event): void
    {
        // only console API requests
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/console')) {
            return;
        }
        if ($event->isMainRequest() === false) {
            return;
        }

        $request = $event->getRequest();

        if ($request->headers->has('authorization')) {
            $this->handleAuthorizationHeader($event);
        } else {
            $this->handleSession($event);
        }
    }

    private function handleAuthorizationHeader(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $authorizationHeader = $request->headers->get('authorization');
        assert(is_string($authorizationHeader));

        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            throw new AccessDeniedHttpException('Authorization header must start with "Bearer ".');
        }

        $apiKey = trim(substr($authorizationHeader, 7));

        if ($apiKey === '') {
            throw new AccessDeniedHttpException('API key is missing or empty.');
        }

        $apiKeyModel = $this->apiKeyService->getByRawKey($apiKey);

        if ($apiKeyModel === null) {
            throw new AccessDeniedHttpException('Invalid API key.');
        }

        $scopes = $apiKeyModel->getScopes();
        $this->verifyScopes($scopes, $event);

        $newsletter = $apiKeyModel->getNewsletter();

        $request->attributes->set(self::RESOLVED_API_KEY_ATTRIBUTE_KEY, $apiKeyModel);
        $request->attributes->set(self::RESOLVED_NEWSLETTER_ATTRIBUTE_KEY, $newsletter);

        $apiKeyUpdates = new UpdateApiKeyDto();
        $apiKeyUpdates->lastAccessedAt = $this->now();
        $this->apiKeyService->updateApiKey($apiKeyModel, $apiKeyUpdates);
    }

    private function handleSession(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $newsletterId = $request->headers->get('x-newsletter-id');
        $isUserLevelEndpoint = count($event->getAttributes(UserLevelEndpoint::class)) > 0;

        $me = $this->auth->me($request);

        if ($me === null) {
            throw new DataCarryingHttpException(
                401,
                [
                    'login_url' => $this->auth->authUrl('login'),
                    'signup_url' => $this->auth->authUrl('signup'),
                ],
                'Unauthorized'
            );
        }

        $user = $me->getUser();
        $organization = $me->getOrganization();

        // user-level endpoints do not have a newsletter ID
        if ($isUserLevelEndpoint === false) {
            if ($newsletterId === null) {
                throw new AccessDeniedHttpException('X-Newsletter-ID is required for this endpoint.');
            }

            $newsletter = $this->newsletterService->getNewsletterById((int)$newsletterId);

            if ($newsletter === null) {
                throw new AccessDeniedHttpException('Invalid newsletter ID.');
            }

            if (!$this->userService->hasAccessToNewsletter($newsletter, $user->id)) {
                throw new AccessDeniedHttpException('You do not have access to this newsletter.');
            }

            $request->attributes->set(self::RESOLVED_NEWSLETTER_ATTRIBUTE_KEY, $newsletter);
        }

        $request->attributes->set(self::RESOLVED_USER_ATTRIBUTE_KEY, $user);

        if ($organization) {
            $request->attributes->set(self::RESOLVED_ORGANIZATION_ATTRIBUTE_KEY, $organization);
        }
    }

    /**
     * @param string[] $scopes
     */
    private function verifyScopes(array $scopes, ControllerEvent $event): void
    {
        $attributes = $event->getAttributes(ScopeRequired::class);
        $scopeRequiredAttribute = $attributes[0] ?? null;

        assert(
            $scopeRequiredAttribute instanceof ScopeRequired,
            'ScopeRequired attribute must be set on the controller method'
        );

        $requiredScope = $scopeRequiredAttribute->scope->value;

        if (!in_array($requiredScope, $scopes, true)) {
            throw new AccessDeniedHttpException(
                "You do not have the required scope '$requiredScope' to access this resource."
            );
        }
    }

    public static function hasUser(Request $request): bool
    {
        return $request->attributes->has(self::RESOLVED_USER_ATTRIBUTE_KEY);
    }

    // only call after hasUser()
    public static function getUser(Request $request): AuthUser
    {
        $user = $request->attributes->get(self::RESOLVED_USER_ATTRIBUTE_KEY);
        assert($user instanceof AuthUser, 'User must be an instance of AuthUser');
        return $user;
    }

    public static function hasNewsletter(Request $request): bool
    {
        return $request->attributes->has(self::RESOLVED_NEWSLETTER_ATTRIBUTE_KEY);
    }

    // make sure the newsletter is set before calling this
    public static function getNewsletter(Request $request): Newsletter
    {
        $newsletter = $request->attributes->get(self::RESOLVED_NEWSLETTER_ATTRIBUTE_KEY);
        assert($newsletter instanceof Newsletter);
        return $newsletter;
    }

    public static function hasOrganization(Request $request): bool
    {
        return $request->attributes->has(self::RESOLVED_ORGANIZATION_ATTRIBUTE_KEY);
    }

    // make sure the organization is set before calling this
    public static function getOrganization(Request $request): AuthUserOrganization
    {
        $organization = $request->attributes->get(self::RESOLVED_ORGANIZATION_ATTRIBUTE_KEY);
        assert($organization instanceof AuthUserOrganization);
        return $organization;
    }

    // make sure the API key is set before calling this
    public static function getApiKey(Request $request): ApiKey
    {
        $apiKey = $request->attributes->get(self::RESOLVED_API_KEY_ATTRIBUTE_KEY);
        assert($apiKey instanceof ApiKey);
        return $apiKey;
    }
}
