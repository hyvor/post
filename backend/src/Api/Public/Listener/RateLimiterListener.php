<?php

namespace App\Api\Public\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest')]
#[AsEventListener(event: KernelEvents::RESPONSE, method: 'onKernelResponse')]
class RateLimiterListener
{

    private const string RATE_LIMIT_HEADERS_KEY = 'rate_limit_headers';

    public function __construct(
        private RateLimiterFactory $publicApiLimiter
    )
    {
    }

    private function isPublicApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/public');
    }

    public function onKernelRequest(RequestEvent $event): void
    {

        $request = $event->getRequest();

        if (!$this->isPublicApiRequest($request)) {
            return;
        }

        $limiter = $this->publicApiLimiter->create($request->getClientIp());
        $limit = $limiter->consume();

        $headers = [
            'RateLimit-Limit' => $limit->getLimit(),
            'RateLimit-Remaining' => $limit->getRemainingTokens(),
            'RateLimit-Reset' => $limit->getRetryAfter()->getTimestamp() - time(),
        ];

        $request->attributes->set(self::RATE_LIMIT_HEADERS_KEY, $headers);

        if ($limit->isAccepted() === false) {
            $response = new Response(
                null,
                Response::HTTP_TOO_MANY_REQUESTS,
                $headers
            );
            $event->setResponse($response);
        }

    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$this->isPublicApiRequest($request)) {
            return;
        }

        if ($request->attributes->has(self::RATE_LIMIT_HEADERS_KEY)) {
            /** @var array<string, ?string> $headers */
            $headers = $request->attributes->get(self::RATE_LIMIT_HEADERS_KEY);
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
        }
    }

}
