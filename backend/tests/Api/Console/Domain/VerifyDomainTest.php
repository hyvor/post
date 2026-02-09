<?php

namespace App\Tests\Api\Console\Domain;

use App\Api\Console\Controller\DomainController;
use App\Api\Console\Object\DomainObject;
use App\Entity\Type\RelayDomainStatus;
use App\Service\Domain\DomainService;
use App\Service\Integration\Relay\Exception\RelayApiException;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(DomainController::class)]
#[CoversClass(DomainService::class)]
#[CoversClass(DomainObject::class)]
class VerifyDomainTest extends WebTestCase
{
    private function mockCreateEmailIdentity(): void
    {
        $callback = function ($method, $url, $options): JsonMockResponse {
            if (str_starts_with($url, 'https://relay.hyvor.com/api/console/domains/verify')) {
                return new JsonMockResponse([
                    'domain' => 'hyvor.com',
                    'status' => 'active',
                    'dkim_checked_at' => 1755455400,
                ]);
            } elseif (str_starts_with($url, 'https://relay.hyvor.com/api/console/sends')) {
                $body = json_decode($options['body'], true);
                $this->assertIsArray($body);
                $this->assertSame('Your domain hyvor.com is verified', $body['subject']);
                $this->assertStringContainsString("Your domain <strong>hyvor.com</strong> has been successfully verified", $body['body_html']);
            }
            return new JsonMockResponse();
        };

        $this->mockRelayClient($callback);
    }

    public function test_verify_domain(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => 1,
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains/' . $domain->getId() . '/verify',
            useSession: true
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsArray($json['domain']);
        $this->assertSame('hyvor.com', $json['domain']['domain']);
        $this->assertSame(RelayDomainStatus::ACTIVE->value, $json['domain']['relay_status']);
    }

    public function test_error_on_relay_call_fails_and_logs(): void
    {
        $httpClient = new MockHttpClient(new MockResponse(info: ['error' => 'host unreachable']));
        $this->container->set(HttpClientInterface::class, $httpClient);

        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);
        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => 1,
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains/' . $domain->getId() . '/verify',
            useSession: true
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Failed to verify domain. Contact support for more details', $json['message']);

        // logging
        $testLogger = $this->getTestLogger();
        $this->assertTrue($testLogger->hasCriticalThatContains('Failed to verify email domain in Hyvor Relay'));
        $record = new ArrayCollection($testLogger->getRecords())
            ->findFirst(fn($index, $record) => $record->message === 'Failed to verify email domain in Hyvor Relay');
        $this->assertNotNull($record);
        $this->assertSame('hyvor.com', $record->context['domain']);
        $this->assertInstanceOf(RelayApiException::class, $record->context['error']);
    }

    public function test_already_verified(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'relay_status' => RelayDomainStatus::ACTIVE,
                'user_id' => 1,
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains/' . $domain->getId() . '/verify',
            useSession: true
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Domain already verified', $json['message']);
    }

    public function test_domain_not_found(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains/99999/verify',
            useSession: true
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Domain not found', $json['message']);
    }

    public function test_user_can_only_verify_their_domain(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => 2,
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains/' . $domain->getId() . '/verify',
            useSession: true
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('You are not the owner of this domain', $json['message']);
    }
}
