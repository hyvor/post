<?php

namespace App\Tests\Api\Console;


use App\Api\Console\RateLimit\RateLimit;
use App\Api\Console\RateLimit\RateLimitListener;
use App\Service\App\RateLimit\RateLimiterProvider;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SendingProfileFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RateLimitListener::class)]
#[CoversClass(RateLimit::class)]
#[CoversClass(RateLimiterProvider::class)]
class RateLimitTest extends WebTestCase
{
    public function test_adds_rate_limit_headers(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'thibault@hyvor.com',
                'list_ids' => [$list->getId()],
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('X-RateLimit-Limit', '100');
        $this->assertResponseHeaderSame('X-RateLimit-Remaining', '99');
        $this->assertResponseHeaderSame('X-RateLimit-Reset', '0');
    }

    public function test_429_on_rate_limited(): void
    {
        $newsletter = NewsletterFactory::createOne(['user_id' => 1]);
        $list = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $rateLimit = new RateLimit();
        /** @var RateLimiterProvider $rateLimiterProvider */
        $rateLimiterProvider = $this->getContainer()->get(RateLimiterProvider::class);

        $limiter = $rateLimiterProvider->rateLimiter($rateLimit->session(), "user:1");
        $limiter->consume(60);
        $limiter->consume(10);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'thibault@hyvor.com',
                'list_ids' => [$list->getId()],
            ],
            useSession: true
        );

        $this->assertResponseStatusCodeSame(429);

        $this->assertResponseHeaderSame('X-RateLimit-Limit', '60');
        $this->assertResponseHeaderSame('X-RateLimit-Remaining', '0');
        $this->assertResponseHeaderSame('X-RateLimit-Reset', '60');
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
        $json = $this->getJson();
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
            $rateLimit->subscriberPerHour(),
            'subscriber_email:' . $email
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
        $this->assertStringContainsString('You have recently requested a subscription confirm link', $json['message']);
        $this->assertStringContainsString('seconds', $json['message']);
    }
}
