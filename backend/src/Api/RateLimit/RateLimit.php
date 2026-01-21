<?php

namespace App\Api\RateLimit;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @phpstan-type RateLimitConfig array{id: string, policy: string, limit: int, interval: string}
 */
class RateLimit
{

    private bool $isDev;

    public function __construct(
        #[Autowire('%kernel.environment%')]
        private readonly string $env = 'prod'
    )
    {
        $this->isDev = $this->env === 'dev';
    }

    /**
     * Rate limit for a user session.
     * 60 per minute
     * @return RateLimitConfig
     */
    public function session(): array
    {
        return [
            'id' => 'console_api_session',
            'policy' => 'fixed_window',
            'limit' => $this->isDev ? 1000 : 60,
            'interval' => '1 minute',
        ];
    }

    /**
     * Rate limit for an API key.
     * 100 per minute
     * @return RateLimitConfig
     */
    public function apiKey(): array
    {
        return [
            'id' => 'console_api_api_key',
            'policy' => 'fixed_window',
            'limit' => $this->isDev ? 1000 : 100,
            'interval' => '1 minute',
        ];
    }

    /**
     * Rate limit for public API per IP.
     * 30 per minute per IP
     * @return RateLimitConfig
     */
    public function publicApi(): array
    {
        return [
            'id' => 'public_api',
            'policy' => 'fixed_window',
            'limit' => $this->isDev ? 1000 : 30,
            'interval' => '1 minute',
        ];
    }

    /**
     * Rate limit for the POST /subscribers endpoint.
     * 1 subscribe per email per minute
     * @return RateLimitConfig
     */
    public function subscriberPerEmailPerMinute(): array
    {
        return [
            'id' => 'public_api_subscriber_per_minute',
            'policy' => 'fixed_window',
            'limit' => 2,
            'interval' => '1 minute',
        ];
    }

    /**
     * Rate limit for the POST /subscribers endpoint.
     * 6 subscribes per email per hour
     * @return RateLimitConfig
     */
    public function subscriberPerEmailPerHour(): array
    {
        return [
            'id' => 'public_api_subscriber_per_hour',
            'policy' => 'fixed_window',
            'limit' => 6,
            'interval' => '1 hour',
        ];
    }

}
