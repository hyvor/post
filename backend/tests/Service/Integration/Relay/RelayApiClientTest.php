<?php

namespace App\Tests\Service\Integration\Relay;

use App\Service\Integration\Relay\RelayApiClient;
use App\Tests\Case\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RelayApiClientTest extends KernelTestCase
{

    private function getApiClient(): RelayApiClient
    {
        /** @var RelayApiClient $relayApiClient */
        $relayApiClient = $this->container->get(RelayApiClient::class);
        return $relayApiClient;
    }

    public function test_4xx_error_handling(): void
    {

        $response = new JsonMockResponse(info: ['http_code' => 422]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

        $apiClient = $this->getApiClient();
        $apiClient->createDomain('test.com');

    }

    // transport exception
    // new MockResponse([new \RuntimeException('Error at transport level')]),

}