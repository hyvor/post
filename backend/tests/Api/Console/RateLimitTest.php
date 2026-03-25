<?php

namespace App\Tests\Api\Console;

use App\Api\Console\RateLimit\RateLimit;
use App\Api\Console\RateLimit\RateLimitListener;
use App\Entity\Newsletter;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\RelayDomainStatus;
use App\Service\App\RateLimit\RateLimiterProvider;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(RateLimitListener::class)]
#[CoversClass(RateLimit::class)]
#[CoversClass(RateLimiterProvider::class)]
class RateLimitTest extends WebTestCase
{
    private function sendTestIssue(Newsletter $newsletter): void
    {
        $this->mockRelayClient(fn() => new JsonMockResponse());

        DomainFactory::createOne([
            'organization_id' => $newsletter->getOrganizationId(),
            'domain' => 'hyvor.com',
            'relay_status' => RelayDomainStatus::ACTIVE
        ]);
        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
                'subject' => 'Test subject',
                'status' => IssueStatus::DRAFT
            ]
        );

        $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/test",
            [
                'emails' => [
                    'nadil@hyvor.com'
                ]
            ]
        );
    }

    public function test_adds_rate_limit_headers(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $this->sendTestIssue($newsletter);
        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame('X-RateLimit-Limit', '10');
        $this->assertResponseHeaderSame('X-RateLimit-Remaining', '9');
        $this->assertResponseHeaderSame('X-RateLimit-Reset', '0');
    }

    public function test_429_on_rate_limited(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $rateLimit = new RateLimit();
        /** @var RateLimiterProvider $rateLimiterProvider */
        $rateLimiterProvider = $this->getContainer()->get(RateLimiterProvider::class);

        $limiter = $rateLimiterProvider->rateLimiter($rateLimit->testIssues(), "test:issue:newsletter:{$newsletter->getId()}");
        $limiter->consume(10);
        $limiter->consume(5);

        $this->sendTestIssue($newsletter);
        $this->assertResponseStatusCodeSame(429);

        $this->assertResponseHeaderSame('X-RateLimit-Limit', '10');
        $this->assertResponseHeaderSame('X-RateLimit-Remaining', '0');
        $this->assertResponseHeaderSame('X-RateLimit-Reset', '3600');
    }

    public function test_no_rate_limit_headers_on_other_endpoints(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'subject' => 'Test subject',
            'status' => IssueStatus::DRAFT
        ]);

        $rateLimit = new RateLimit();
        /** @var RateLimiterProvider $rateLimiterProvider */
        $rateLimiterProvider = $this->getContainer()->get(RateLimiterProvider::class);

        $limiter = $rateLimiterProvider->rateLimiter($rateLimit->testIssues(), "test:issue:newsletter:{$newsletter->getId()}");
        $limiter->consume(10);
        $limiter->consume(5);

        $this->consoleApi(
            $newsletter,
            'GET',
            "/issues/{$issue->getId()}/test",
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseNotHasHeader('X-RateLimit-Limit');
        $this->assertResponseNotHasHeader('X-RateLimit-Remaining');
        $this->assertResponseNotHasHeader('X-RateLimit-Reset');
    }
}