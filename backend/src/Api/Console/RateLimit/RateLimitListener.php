<?php

namespace App\Api\Console\RateLimit;

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

    private const string RATE_LIMIT_HEADERS_ATTRIBUTE_KEY = 'console_api_rate_limit_headers';

    private function isConsoleApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/console');
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

    public function onController(ControllerEvent $controllerEvent): void
    {
        if ($controllerEvent->isMainRequest() === false) {
            return;
        }

        $request = $controllerEvent->getRequest();

        // Apply subscribe-specific rate limiting for public form submissions
        if ($this->isSubscribePostRequest($request)) {
            $this->applySubscribeRateLimit($request);
            return; // Skip general rate limiting for this endpoint
        }

        if (!$this->isConsoleApiRequest($request)) {
            return;
        }

        $limiter = $this->getRateLimiter($request);
        $limit = $limiter->consume();

        $resetIn = max($limit->getRetryAfter()->getTimestamp() - time(), 0);
        $request->attributes->set(self::RATE_LIMIT_HEADERS_ATTRIBUTE_KEY, [
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

        $perMinuteLimiter = $this->rateLimiterProvider->rateLimiter(
            $this->rateLimit->subscriberPerMinute(),
            'subscriber_email:' . $email
        );
        $perMinuteLimit = $perMinuteLimiter->consume();

        if ($perMinuteLimit->isAccepted() === false) {
            $resetIn = max($perMinuteLimit->getRetryAfter()->getTimestamp() - time(), 0);
            throw new TooManyRequestsHttpException(
                message: 'You have recently requested a subscription confirm link. Please try again in ' . $resetIn . ' seconds.',
            );
        }

        $perHourLimiter = $this->rateLimiterProvider->rateLimiter(
            $this->rateLimit->subscriberPerHour(),
            'subscriber_email:' . $email
        );
        $perHourLimit = $perHourLimiter->consume();

        if ($perHourLimit->isAccepted() === false) {
            $resetIn = max($perHourLimit->getRetryAfter()->getTimestamp() - time(), 0);
            throw new TooManyRequestsHttpException(
                message: 'You have recently requested a subscription confirm link. Please try again in ' . $resetIn . ' seconds.',
            );
        }
    }

    public function onResponse(ResponseEvent $responseEvent): void
    {
        if ($responseEvent->isMainRequest() === false) {
            return;
        }

        $request = $responseEvent->getRequest();
        if (!$this->isConsoleApiRequest($request)) {
            return;
        }

        $response = $responseEvent->getResponse();

        if ($request->attributes->has(self::RATE_LIMIT_HEADERS_ATTRIBUTE_KEY)) {
            /** @var array<string, string|int> $rateLimitHeaders */
            $rateLimitHeaders = $request->attributes->get(self::RATE_LIMIT_HEADERS_ATTRIBUTE_KEY);
            foreach ($rateLimitHeaders as $header => $value) {
                $response->headers->set($header, (string)$value);
            }
        }
    }

}
