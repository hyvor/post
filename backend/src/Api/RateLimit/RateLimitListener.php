<?php

namespace App\Api\RateLimit;

use App\Entity\Newsletter;
use App\Service\App\RateLimit\RateLimiterProvider;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\AccessType;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleApiAuthorizationListenerAbstract;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleAuthResults;
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

    private const string RATE_LIMIT_HEADERS_ATTRIBUTE_KEY = 'rate_limit_headers';

    private function isPublicApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/public');
    }

    private function isConsoleApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/console');
    }

    private function isSubscribePostRequest(Request $request): bool
    {
        return $request->getMethod() === 'POST'
            && $request->getPathInfo() === '/api/public/form/subscribe';
    }

    private function isTestIssueEndpoint(Request $request): bool
    {
        return $request->getMethod() === 'POST'
            && preg_match('#^/api/console/issues/\d+/test$#', $request->getPathInfo()) === 1;
    }

    private function getRateLimiter(ConsoleAuthResults $consoleAuth): LimiterInterface
    {
        // check if this is a session request (user logged in)
        $user = $consoleAuth->getNullableUser();
        if ($consoleAuth->getAccessType() === AccessType::SESSION && $user !== null) {
            return $this->rateLimiterProvider->rateLimiter($this->rateLimit->session(), "user:" . $user->id);
        }

        // product API key or cloud token
        return $this->rateLimiterProvider->rateLimiter($this->rateLimit->apiKey(), $consoleAuth->getSourceString());
    }

    private function getTestIssueRateLimiter(ConsoleAuthResults $consoleAuth): LimiterInterface
    {
        $newsletter = $consoleAuth->getResource();
        assert($newsletter instanceof Newsletter);

        return $this->rateLimiterProvider->rateLimiter(
            $this->rateLimit->testIssues(),
            'test:issue:newsletter:' . $newsletter->getId(),
        );
    }

    /**
     * @param string $errorMessage Message with %d placeholder for resetIn seconds
     */
    private function consume(
        Request $request,
        LimiterInterface $limiter,
        string $errorMessage = 'Rate limit exceeded. Please try again in %d seconds.',
    ): void {
        $limit = $limiter->consume();
        $resetIn = max($limit->getRetryAfter()->getTimestamp() - time(), 0);

        // when multiple limits apply, keep the headers of the one closest to being hit
        $currentHeaders = $request->attributes->get(self::RATE_LIMIT_HEADERS_ATTRIBUTE_KEY);
        if (!is_array($currentHeaders) || $limit->getRemainingTokens() <= $currentHeaders['X-RateLimit-Remaining']) {
            $request->attributes->set(self::RATE_LIMIT_HEADERS_ATTRIBUTE_KEY, [
                'X-RateLimit-Limit' => $limit->getLimit(),
                'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
                'X-RateLimit-Reset' => $resetIn,
            ]);
        }

        if ($limit->isAccepted() === false) {
            throw new TooManyRequestsHttpException(
                retryAfter: $resetIn,
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
            $this->applyPublicApiRateLimit($request);

            if ($this->isSubscribePostRequest($request)) {
                $this->applySubscribeRateLimit($request);
            }
        } else if ($this->isConsoleApiRequest($request)) {
            $this->applyConsoleApiRateLimit($request);
        }
        // Other API endpoints (machine, sudo, ...) - no rate limiting
    }

    private function applyPublicApiRateLimit(Request $request): void
    {
        $this->consume(
            $request,
            $this->rateLimiterProvider->rateLimiter(
                $this->rateLimit->publicApi(),
                'public_ip:' . $request->getClientIp()
            ),
        );
    }

    private function applyConsoleApiRateLimit(Request $request): void
    {
        $consoleAuth = $request->attributes->get(ConsoleApiAuthorizationListenerAbstract::ATTRIBUTE_KEY);
        if (!$consoleAuth instanceof ConsoleAuthResults) {
            return; // auth bypassed paths (ex: /api/console/init)
        }

        $this->consume($request, $this->getRateLimiter($consoleAuth));

        // the test issue endpoint has a stricter limit on top of the general one
        if ($this->isTestIssueEndpoint($request)) {
            $this->consume($request, $this->getTestIssueRateLimiter($consoleAuth));
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

        $this->consume(
            $request,
            $this->rateLimiterProvider->rateLimiter($this->rateLimit->subscriberPerEmailPerMinute(), $identifier),
            'You have recently requested a subscription confirm link. Please try again in %d seconds.'
        );

        $this->consume(
            $request,
            $this->rateLimiterProvider->rateLimiter($this->rateLimit->subscriberPerEmailPerHour(), $identifier),
            'You have recently requested a subscription confirm link. Please try again in %d seconds.'
        );
    }

    public function onResponse(ResponseEvent $responseEvent): void
    {
        if ($responseEvent->isMainRequest() === false) {
            return;
        }

        $request = $responseEvent->getRequest();

        if (!$request->attributes->has(self::RATE_LIMIT_HEADERS_ATTRIBUTE_KEY)) {
            return;
        }

        /** @var array<string, string|int> $rateLimitHeaders */
        $rateLimitHeaders = $request->attributes->get(self::RATE_LIMIT_HEADERS_ATTRIBUTE_KEY);
        foreach ($rateLimitHeaders as $header => $value) {
            $responseEvent->getResponse()->headers->set($header, (string)$value);
        }
    }

}
