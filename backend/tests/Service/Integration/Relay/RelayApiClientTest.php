<?php

namespace App\Tests\Service\Integration\Relay;

use App\Service\Integration\Relay\Exception\RelayApiException;
use App\Service\Integration\Relay\RelayApiClient;
use App\Tests\Case\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
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

    public function test_3xx_error_handling(): void
    {
        $response = new JsonMockResponse(['message' => 'Moved to another location'], ['http_code' => 301]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();

        $this->expectException(RelayApiException::class);
        $this->expectExceptionMessage('Max attempts reached, last error: Moved to another location');
        $apiClient->createDomain('test.com');
    }

    public function test_4xx_error_handling(): void
    {
        $response = new JsonMockResponse(info: ['http_code' => 422]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();

        $this->expectException(RelayApiException::class);
        $this->expectExceptionMessage('Max attempts reached, last error: Unknown error');
        $apiClient->createDomain('test.com');
    }

    public function test_4xx_error_handling_2(): void
    {
        $response = new JsonMockResponse(['not_message' => 'Forbidden'], ['http_code' => 403]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();

        $this->expectException(RelayApiException::class);
        $this->expectExceptionMessage('Max attempts reached, last error: Unknown error');
        $apiClient->createDomain('test.com');
    }

    public function test_5xx_error_handling(): void
    {
        $response = new MockResponse('Server is down', ['http_code' => 500]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();

        $this->expectException(RelayApiException::class);
        $this->expectExceptionMessage('Max attempts reached, last error: Unknown error');
        $apiClient->createDomain('test.com');
    }

    // transport exception
    // new MockResponse([new \RuntimeException('Error at transport level')]),

}
