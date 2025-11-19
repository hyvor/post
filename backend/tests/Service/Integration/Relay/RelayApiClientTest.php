<?php

namespace App\Tests\Service\Integration\Relay;

use App\Service\Integration\Relay\Exception\RelayApiException;
use App\Service\Integration\Relay\RelayApiClient;
use App\Tests\Case\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(RelayApiClient::class)]
class RelayApiClientTest extends KernelTestCase
{
    private function getApiClient(): RelayApiClient
    {
        /** @var RelayApiClient $relayApiClient */
        $relayApiClient = $this->container->get(RelayApiClient::class);
        return $relayApiClient;
    }

    private function getMockEmail(): Email
    {
        $email = new Email();

        $email->from(new Address('nadil@hyvor.com', 'Nadil'));
        $email->to(new Address('supun@hyvor.com', 'Supun'));
        $email->subject('Test Subject');
        $email->text('This is a test email body in text format.');
        $email->html('<p>This is a test email body in <strong>HTML</strong> format.</p>');
        $email->getHeaders()
            ->addTextHeader('X-Custom-Header', 'CustomValue');

        return $email;
    }

    public function test_create_domain(): void
    {
        $response = new JsonMockResponse([
            'domain' => 'test.com',
            'dkim_host' => 'dkim_host',
            'dkim_txt_value' => 'dkim_txt_value',
        ]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();
        $result = $apiClient->createDomain('test.com');

        $this->assertSame('test.com', $result->domain);
        $this->assertSame('dkim_host', $result->dkim_host);
        $this->assertSame('dkim_txt_value', $result->dkim_txt_value);
    }

    public function test_send_email(): void
    {
        $email = $this->getMockEmail();
        $response = new JsonMockResponse([
            'id' => 12345,
            'message_id' => 'message_id',
        ]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();
        $result = $apiClient->sendEmail($email, 'newsletter-send-8395');

        $this->assertContains('X-Idempotency-Key: newsletter-send-8395', $response->getRequestOptions()['headers']);
        $this->assertSame(12345, $result->id);
        $this->assertSame('message_id', $result->message_id);
    }

    public function test_4xx_error_handling(): void
    {
        $response = new JsonMockResponse(['message' => "Test 4xx occurred"], ['http_code' => 422]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();

        $this->expectException(RelayApiException::class);
        $this->expectExceptionMessage('Test 4xx occurred');
        $apiClient->createDomain('test.com');
    }

    public function test_3xx_error_handling_without_message_key(): void
    {
        $response = new JsonMockResponse(['not_message' => 'Moved to another location'], ['http_code' => 301]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();

        $this->expectException(RelayApiException::class);
        $this->expectExceptionMessage('Unknown error');
        $apiClient->createDomain('test.com');
    }

    public function test_4xx_error_handling_with_decoding_exception(): void
    {
        $response = new MockResponse('Forbidden', ['http_code' => 403]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();

        $this->expectException(RelayApiException::class);
        $this->expectExceptionMessage('Unknown error');
        $apiClient->createDomain('test.com');
    }

    public function test_429_error_handling(): void
    {
        $response = new JsonMockResponse(['message' => "You've been bothering me a lot"], ['http_code' => 429]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient([
            $response,
            $response,
            $response,
        ]));

        $apiClient = $this->getApiClient();

        $this->expectException(RelayApiException::class);
        $this->expectExceptionMessage("Max attempts reached, last error: You've been bothering me a lot");
        $apiClient->createDomain('test.com');
    }

    public function test_5xx_error_handling(): void
    {
        $response = new JsonMockResponse(['message' => 'Server is down'], ['http_code' => 500]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient([
            $response,
            $response,
            $response,
        ]));

        $apiClient = $this->getApiClient();

        $this->expectException(RelayApiException::class);
        $this->expectExceptionMessage('Max attempts reached, last error: Server is down');
        $apiClient->createDomain('test.com');
    }

    public function test_transport_exception_handling(): void
    {
        $response = new MockResponse([new \RuntimeException('Error at transport level')]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();

        $this->expectException(RelayApiException::class);
        $this->expectExceptionMessage('Max attempts reached, last error: Error at transport level');
        $apiClient->createDomain('test.com');
    }
}
