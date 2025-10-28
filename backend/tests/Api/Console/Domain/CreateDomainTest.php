<?php

namespace App\Tests\Api\Console\Domain;

use App\Api\Console\Controller\DomainController;
use App\Api\Console\Input\Domain\CreateDomainInput;
use App\Api\Console\Object\DomainObject;
use App\Entity\Domain;
use App\Service\Domain\DomainService;
use App\Service\Integration\Relay\Exception\RelayApiException;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(DomainController::class)]
#[CoversClass(DomainService::class)]
#[CoversClass(CreateDomainInput::class)]
#[CoversClass(DomainObject::class)]
class CreateDomainTest extends WebTestCase
{

    private function mockHttpClient(): void
    {
        $callback = function ($method, $url, $options): JsonMockResponse {

            $this->assertSame('POST', $method);
            $this->assertSame('https://relay.hyvor.com/api/console/domains', $url);
            $this->assertSame('{"domain":"hyvor.com"}', $options['body']);
            $this->assertContains('Content-Type: application/json', $options['headers']);
            $this->assertContains('Authorization: Bearer test-relay-key', $options['headers']);

            return new JsonMockResponse([
                'id' => 1,
                'domain' => 'hyvor.com',
                'dkim_host' => 'rly2025',
                'dkim_txt_value' => 'v=DKIM1; k=rsa; p=...',
            ]);
        };

        parent::mockRelayClient($callback);
    }

    public function test_create_domain(): void
    {
        $this->mockHttpClient();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains',
            [
                'domain' => 'hyvor.com',
            ],
            useSession: true
        );
        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $domainId = $json['id'];
        $this->assertIsInt($domainId);
        $this->assertSame('hyvor.com', $json['domain']);

        $domainRepository = $this->em->getRepository(Domain::class);
        $domain = $domainRepository->find($domainId);
        $this->assertNotNull($domain);
        $this->assertSame('hyvor.com', $domain->getDomain());
    }

    public function test_create_system_domain_fails(): void
    {
        $this->mockHttpClient();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains',
            [
                'domain' => 'hyvorpost.email',
            ],
            useSession: true
        );
        $this->assertSame(400, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertSame('This domain is reserved and cannot be registered', $json['message']);
    }

    public function test_create_domain_invalid(): void
    {
        $this->mockHttpClient();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains',
            [
                'domain' => 'invalid-domain',
            ],
            useSession: true
        );
        $this->assertSame(422, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertIsArray($json['violations']);
        $violation = $json['violations'][0];
        $this->assertIsArray($violation);
        $this->assertSame('The domain must be a valid domain name.', $violation['message']);
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_create_domain_already_exists(bool $current): void
    {
        $this->mockHttpClient();

        Clock::set(new MockClock('2025-02-21'));

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => $current ? 1 : 2
            ]
        );

        $response = $this->consoleApi(
            null,
            'POST',
            '/domains',
            [
                'domain' => 'hyvor.com',
            ]
        );
        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame(
            $current ?
                'This domain is already registered' :
                'This domain is already registered by another user',
            $json['message']
        );
    }

    public function test_error_on_relay_call_fails_and_logs(): void
    {
        $httpClient = new MockHttpClient(new MockResponse(info: ['error' => 'host unreachable']));
        $this->container->set(HttpClientInterface::class, $httpClient);

        $response = $this->consoleApi(
            null,
            'POST',
            '/domains',
            [
                'domain' => 'hyvor.com',
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Failed to create domain. Contact support for more details', $json['message']);

        // logging
        $testLogger = $this->getTestLogger();
        $this->assertTrue($testLogger->hasCriticalThatContains('Failed to create email domain in Hyvor Relay'));
        $record = new ArrayCollection($testLogger->getRecords())
            ->findFirst(fn($index, $record) => $record->message === 'Failed to create email domain in Hyvor Relay');
        $this->assertNotNull($record);
        $this->assertSame('hyvor.com', $record->context['domain']);
        $this->assertInstanceOf(RelayApiException::class, $record->context['error']);
    }
}
