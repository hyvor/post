<?php

namespace App\Service\Integration\Relay;

use App\Service\AppConfig;
use App\Service\Integration\Relay\Exception\RelayApiException;
use App\Service\Integration\Relay\Response\CreateDomainResponse;
use App\Service\Integration\Relay\Response\DeleteDomainResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RelayApiClient
{

    public function __construct(
        private AppConfig $appConfig,
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer
    )
    {
    }

    /**
     * @template T of object
     * @param class-string<T> $classToDeserialize
     * @param array<string, mixed> $data
     * @return T
     * @throws RelayApiException
     */
    private function callApi(
        string $method,
        string $endpoint,
        string $classToDeserialize,
        array $data = [],
    ) {

        try {
            $response = $this->httpClient->request(
                $method,
                $this->appConfig->getRelayUrl() . '/api/console/' . ltrim($endpoint, '/'),
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->appConfig->getRelayApiKey(),
                    ],
                    'json' => $data,
                ]
            );

            return $this->serializer->deserialize($response->getContent(), $classToDeserialize, 'json');

        } catch (TransportExceptionInterface|HttpExceptionInterface $e) {
            throw new RelayApiException($e->getMessage());
        }

    }

    /**
     * @throws RelayApiException
     */
    public function createDomain(string $domain): CreateDomainResponse
    {
        return $this->callApi(
            'POST',
            '/domains',
            CreateDomainResponse::class,
            [
                'domain' => $domain
            ]
        );
    }

    /**
     * @throws RelayApiException
     */
    public function deleteDomain(string $domain): DeleteDomainResponse
    {
        return $this->callApi(
            'DELETE',
            '/domains',
            DeleteDomainResponse::class,
            [
                'domain' => $domain
            ]
        );
    }

}