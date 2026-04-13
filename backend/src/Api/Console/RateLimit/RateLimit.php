<?php

namespace App\Api\Console\RateLimit;

/**
 * @phpstan-type RateLimitConfig array{id: string, policy: string, limit: int, interval: string}
 */
class RateLimit
{
    /**
     * Rate limit for the POST /issues/{id}/test endpoint.
     * 1 per minute
     * @return RateLimitConfig
     */
    public function testIssues(): array
    {
        return [
            'id' => 'console_api_test_issues',
            'policy' => 'fixed_window',
            'limit' => 10,
            'interval' => '1 hour',
        ];
    }
}