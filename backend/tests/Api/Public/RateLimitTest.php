<?php

namespace App\Tests\Api\Public;

use App\Api\RateLimit\RateLimit;
use App\Api\RateLimit\RateLimitListener;
use App\Service\App\RateLimit\RateLimiterProvider;
use App\Service\Ip\IpService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RateLimitListener::class)]
#[CoversClass(RateLimit::class)]
#[CoversClass(RateLimiterProvider::class)]
#[CoversClass(IpService::class)]
class RateLimitTest extends WebTestCase
{
    public function test_adds_rate_limit_headers(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subdomain = $newsletter->getSubdomain();

        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $subdomain,
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('X-RateLimit-Limit', '30');
        $this->assertResponseHeaderSame('X-RateLimit-Remaining', '29');
        $this->assertTrue($this->client->getResponse()->headers->has('X-RateLimit-Reset'));
    }

    public function test_429_on_rate_limited(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subdomain = $newsletter->getSubdomain();

        // Make 30 requests to exhaust the rate limit
        for ($i = 0; $i < 30; $i++) {
            $this->publicApi('POST', '/form/init', [
                'newsletter_subdomain' => $subdomain,
            ]);
        }

        // 31st request should be rate limited
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $subdomain,
        ]);

        $this->assertResponseStatusCodeSame(429);
        $this->assertResponseHeaderSame('X-RateLimit-Limit', '30');
        $this->assertResponseHeaderSame('X-RateLimit-Remaining', '0');
        $this->assertTrue($this->client->getResponse()->headers->has('Retry-After'));
    }

    public function test_rate_limit_per_ip(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subdomain = $newsletter->getSubdomain();

        // Make 30 requests from first IP
        for ($i = 0; $i < 30; $i++) {
            $this->publicApi('POST', '/form/init', [
                'newsletter_subdomain' => $subdomain,
            ], clientIp: '192.168.1.1');
        }

        // 31st request from first IP should be rate limited
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $subdomain,
        ], clientIp: '192.168.1.1');

        $this->assertResponseStatusCodeSame(429);

        // Request from different IP should succeed
        $response = $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $subdomain,
        ], clientIp: '192.168.1.2');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('X-RateLimit-Remaining', '29');
    }

    public function test_rate_limit_per_cloudflare_ip(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subdomain = $newsletter->getSubdomain();

        // all requests come from the same Cloudflare IP
        for ($i = 0; $i < 30; $i++) {
            $this->publicApi('POST', '/form/init', [
                'newsletter_subdomain' => $subdomain,
            ], ['CF-Connecting-IP' => '203.0.113.1'], clientIp: '172.64.0.1');
        }

        $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $subdomain,
        ], ['CF-Connecting-IP' => '203.0.113.1'], clientIp: '172.64.0.1');

        $this->assertResponseStatusCodeSame(429);

        // another client behind the same Cloudflare IP is not limited
        $this->publicApi('POST', '/form/init', [
            'newsletter_subdomain' => $subdomain,
        ], ['CF-Connecting-IP' => '203.0.113.2'], clientIp: '172.64.0.1');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('X-RateLimit-Remaining', '29');
    }
}
