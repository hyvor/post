<?php

namespace App\Api\RateLimit;

use App\Api\Console\Authorization\AuthorizationListener;
use App\Service\App\RateLimit\RateLimiterProvider;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\LimiterInterface;

// priority less than AuthorizationListener
// more than IdempotencyListener
#[AsEventListener(event: KernelEvents::CONTROLLER, method: 'onController', priority: 150)]
#[AsEventListener(event: KernelEvents::RESPONSE, method: 'onResponse')]
class RateLimitListener
{

    public function __construct(
        private RateLimit $rateLimit,
        private RateLimiterProvider $rateLimiterProvider,
    ) {
    }

    private const string CONSOLE_RATE_LIMIT_HEADERS_KEY = 'console_api_rate_limit_headers';
    private const string PUBLIC_RATE_LIMIT_HEADERS_KEY = 'public_api_rate_limit_headers';

    private function isPublicApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/public');
    }

    private function isConsoleApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/console');
    }

    private function isMachineApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/machine');
    }

    private function isSubscribePostRequest(Request $request): bool
    {
        return $request->getMethod() === 'POST'
            && $request->getPathInfo() === '/api/public/form/subscribe';
    }

    private function getRateLimiter(Request $request): LimiterInterface
    {
        // check if this is a session request (user logged in)
        if (AuthorizationListener::hasUser($request)) {
            $user = AuthorizationListener::getUser($request);
            return $this->rateLimiterProvider->rateLimiter($this->rateLimit->session(), "user:" . $user->id);
        }

        $apiKey = AuthorizationListener::getApiKey($request);

        return $this->rateLimiterProvider->rateLimiter($this->rateLimit->apiKey(), 'api_key:' . $apiKey->getId());
    }


    /**
     * @param array{id: string, policy: string, limit: int, interval: string} $rateLimitConfig
     * @param string $identifier
     * @param string $errorMessage Message with %d placeholder for resetIn seconds
     * @return void
     */
    private function checkRateLimit(array $rateLimitConfig, string $identifier, string $errorMessage): void
    {
        $limiter = $this->rateLimiterProvider->rateLimiter($rateLimitConfig, $identifier);
        $limit = $limiter->consume();

        if ($limit->isAccepted() === false) {
            $resetIn = max($limit->getRetryAfter()->getTimestamp() - time(), 0);
            throw new TooManyRequestsHttpException(
                message: sprintf($errorMessage, $resetIn),
            );
        }
    }

    public function onController(ControllerEvent $controllerEvent): void
    {
        if ($controllerEvent->isMainRequest() === false) {
            return;
        }

        $request = $controllerEvent->getRequest();

        // Unified rate limiting logic
        if ($this->isPublicApiRequest($request)) {
            if ($this->isSubscribePostRequest($request)) {
                $this->applySubscribeRateLimit($request);
            }

            $this->applyPublicApiRateLimit($request);

        } else if ($this->isConsoleApiRequest($request)) {
            $this->applyConsoleApiRateLimit($request);

        } else if ($this->isMachineApiRequest($request)) {
            // Machine API - no rate limiting
            return;
        }
        // Other API endpoints - no rate limiting
    }

    private function applyPublicApiRateLimit(Request $request): void
    {
        $limiter = $this->rateLimiterProvider->rateLimiter(
            $this->rateLimit->publicApi(),
            'public_ip:' . $request->getClientIp()
        );
        $limit = $limiter->consume();

        $resetIn = max($limit->getRetryAfter()->getTimestamp() - time(), 0);
        $request->attributes->set(self::PUBLIC_RATE_LIMIT_HEADERS_KEY, [
            'RateLimit-Limit' => $limit->getLimit(),
            'RateLimit-Remaining' => $limit->getRemainingTokens(),
            'RateLimit-Reset' => $resetIn,
        ]);

        if ($limit->isAccepted() === false) {
            throw new TooManyRequestsHttpException(
                message: 'Rate limit exceeded. Please try again in ' . $resetIn . ' seconds.',
            );
        }
    }

    private function applyConsoleApiRateLimit(Request $request): void
    {
        $limiter = $this->getRateLimiter($request);
        $limit = $limiter->consume();

        $resetIn = max($limit->getRetryAfter()->getTimestamp() - time(), 0);
        $request->attributes->set(self::CONSOLE_RATE_LIMIT_HEADERS_KEY, [
            'X-RateLimit-Limit' => $limit->getLimit(),
            'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
            'X-RateLimit-Reset' => $resetIn,
        ]);

        if ($limit->isAccepted() === false) {
            throw new TooManyRequestsHttpException(
                message: 'Rate limit exceeded. Please try again later in ' . $resetIn . ' seconds.',
            );
        }
    }

    private function applySubscribeRateLimit(Request $request): void
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return;
        }

        if (!isset($data['email']) || !is_string($data['email'])) {
            return; // Skip rate limiting if email is not provided or invalid
        }

        $email = $data['email'];
        $identifier = 'subscriber_email:' . $email;

        $this->checkRateLimit(
            $this->rateLimit->subscriberPerEmailPerMinute(),
            $identifier,
            'You have recently requested a subscription confirm link. Please try again in %d seconds.'
        );

        $this->checkRateLimit(
            $this->rateLimit->subscriberPerEmailPerHour(),
            $identifier,
            'You have recently requested a subscription confirm link. Please try again in %d seconds.'
        );
    }

    public function onResponse(ResponseEvent $responseEvent): void
    {
        if ($responseEvent->isMainRequest() === false) {
            return;
        }

        $request = $responseEvent->getRequest();
        $response = $responseEvent->getResponse();

        // Add rate limit headers for Console API
        if ($this->isConsoleApiRequest($request)) {
            if ($request->attributes->has(self::CONSOLE_RATE_LIMIT_HEADERS_KEY)) {
                /** @var array<string, string|int> $rateLimitHeaders */
                $rateLimitHeaders = $request->attributes->get(self::CONSOLE_RATE_LIMIT_HEADERS_KEY);
                foreach ($rateLimitHeaders as $header => $value) {
                    $response->headers->set($header, (string)$value);
                }
            }
        }

        // Add rate limit headers for Public API
        if ($this->isPublicApiRequest($request)) {
            if ($request->attributes->has(self::PUBLIC_RATE_LIMIT_HEADERS_KEY)) {
                /** @var array<string, string|int> $rateLimitHeaders */
                $rateLimitHeaders = $request->attributes->get(self::PUBLIC_RATE_LIMIT_HEADERS_KEY);
                foreach ($rateLimitHeaders as $header => $value) {
                    $response->headers->set($header, (string)$value);
                }
            }
        }
    }

}
