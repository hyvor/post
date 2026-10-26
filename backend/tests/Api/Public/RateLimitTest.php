<?php

namespace App\Tests\Api\Public;

use App\Api\RateLimit\RateLimit;
use App\Api\RateLimit\RateLimitListener;
use App\Service\App\RateLimit\RateLimiterProvider;
use App\Service\Ip\IpService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SendingProfileFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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

    public function test_subscribe_endpoint_applies_per_minute_email_rate_limit(): void
    {
        $this->mockRelayClient();
        $newsletter = NewsletterFactory::createOne();
        SendingProfileFactory::createOne(['newsletter' => $newsletter]);
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->publicApi(
            'POST',
            '/form/subscribe',
            [
                'newsletter_subdomain' => $newsletter->getSubdomain(),
                'email' => 'test@example.com',
                'list_ids' => [$list->getId()]
            ]
        );

        $response = $this->publicApi(
            'POST',
            '/form/subscribe',
            [
                'newsletter_subdomain' => $newsletter->getSubdomain(),
                'email' => 'test@example.com',
                'list_ids' => [$list->getId()]
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $response = $this->publicApi(
            'POST',
            '/form/subscribe',
            [
                'newsletter_subdomain' => $newsletter->getSubdomain(),
                'email' => 'test@example.com',
                'list_ids' => [$list->getId()]
            ]
        );

        $this->assertResponseStatusCodeSame(429);
        // headers of the per-minute subscribe limit, not the public API one
        $this->assertResponseHeaderSame('X-RateLimit-Limit', '2');
        $this->assertResponseHeaderSame('X-RateLimit-Remaining', '0');
        $this->assertTrue($this->client->getResponse()->headers->has('Retry-After'));
        $json = $this->getJson();
        $this->assertIsString($json['message']);
        $this->assertStringContainsString('You have recently requested a subscription confirm link', $json['message']);
        $this->assertStringContainsString('seconds', $json['message']);
    }

    public function test_subscribe_endpoint_different_emails_not_rate_limited(): void
    {
        $this->mockRelayClient();
        $newsletter = NewsletterFactory::createOne();
        SendingProfileFactory::createOne(['newsletter' => $newsletter]);
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->publicApi(
            'POST',
            '/form/subscribe',
            [
                'newsletter_subdomain' => $newsletter->getSubdomain(),
                'email' => 'test1@example.com',
                'list_ids' => [$list->getId()]
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $response = $this->publicApi(
            'POST',
            '/form/subscribe',
            [
                'newsletter_subdomain' => $newsletter->getSubdomain(),
                'email' => 'test2@example.com',
                'list_ids' => [$list->getId()]
            ]
        );

        $this->assertResponseStatusCodeSame(200);
    }

    public function test_subscribe_endpoint_hourly_rate_limit(): void
    {
        $this->mockRelayClient();
        $newsletter = NewsletterFactory::createOne();
        SendingProfileFactory::createOne(['newsletter' => $newsletter]);
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $rateLimit = new RateLimit();
        /** @var RateLimiterProvider $rateLimiterProvider */
        $rateLimiterProvider = $this->getContainer()->get(RateLimiterProvider::class);

        $email = 'test@example.com';

        // Consume the hourly limit (6 requests)
        $perHourLimiter = $rateLimiterProvider->rateLimiter(
            $rateLimit->subscriberPerEmailPerHour(),
            'subscriber_email:' . hash('sha256', $email)
        );
        $perHourLimiter->consume(6);


        $response = $this->publicApi(
            'POST',
            '/form/subscribe',
            [
                'newsletter_subdomain' => $newsletter->getSubdomain(),
                'email' => $email,
                'list_ids' => [$list->getId()]
            ]
        );

        $this->assertResponseStatusCodeSame(429);
        $json = $this->getJson();
        $this->assertIsString($json['message']);
        $this->assertStringContainsString('You have recently requested a subscription confirm link', $json['message']);
        $this->assertStringContainsString('seconds', $json['message']);
    }

    public function test_subscribe_without_email_only_applies_public_rate_limit(): void
    {
        $this->publicApi('POST', '/form/subscribe', [
            'newsletter_subdomain' => 'test',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('X-RateLimit-Limit', '30');
    }

    public function test_subscribe_with_invalid_json_only_applies_public_rate_limit(): void
    {
        $this->client->request(
            'POST',
            '/api/public/form/subscribe',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: 'not json',
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('X-RateLimit-Limit', '30');
    }

    public function test_ignores_sub_requests(): void
    {
        /** @var RateLimitListener $listener */
        $listener = $this->getContainer()->get(RateLimitListener::class);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/api/public/form/init', 'POST');

        $listener->onController(
            new ControllerEvent($kernel, fn() => null, $request, HttpKernelInterface::SUB_REQUEST),
        );
        $this->assertFalse($request->attributes->has('rate_limit_headers'));

        $request->attributes->set('rate_limit_headers', ['X-RateLimit-Limit' => 30]);
        $response = new Response();
        $listener->onResponse(
            new ResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response),
        );
        $this->assertFalse($response->headers->has('X-RateLimit-Limit'));
    }
}
