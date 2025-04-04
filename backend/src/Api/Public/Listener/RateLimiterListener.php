<?php

namespace App\Api\Public\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: KernelEvents::REQUEST)]
class RateLimiterListener
{

    public function __construct(
        private RateLimiterFactory $publicApiLimiter
    )
    {
    }

    public function __invoke(RequestEvent $event): void
    {

        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/public')) {
            return;
        }

        $limiter = $this->publicApiLimiter->create($request->getClientIp());
        $limit = $limiter->consume();

        if ($limit->isAccepted() === false) {
            $response = new Response(
                null,
                Response::HTTP_TOO_MANY_REQUESTS,
                [
                    'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
                    'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp() - time(),
                    'X-RateLimit-Limit' => $limit->getLimit(),
                ]
            );
        }

        dd($event->getRequest()->getPathInfo());
    }

}
