<?php

namespace App\Service\Integration\Relay;

use App\Service\AppConfig;
use App\Service\Integration\Relay\Exception\RelayApiException;
use App\Service\Integration\Relay\Response\CreateDomainResponse;
use App\Service\Integration\Relay\Response\SendEmailResponse;
use Symfony\Component\Mime\Email;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RelayApiClient
{

    public function __construct(
        private AppConfig           $appConfig,
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
        array  $data = [],
    )
    {
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

            if ($response->getStatusCode() !== 200) {
                $json = $response->toArray(false);
                throw new RelayApiException($json['message'] ?? 'Unknown error');
            }

            return $this->serializer->deserialize($response->getContent(), $classToDeserialize, 'json');

        } catch (TransportExceptionInterface|HttpExceptionInterface|DecodingExceptionInterface $e) {
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

    public function sendEmail(Email $email): SendEmailResponse
    {
        $additionalHeaders = [];

        foreach ($email->getHeaders()->all() as $header) {
            $name = $header->getName();
            if (
                strtolower($name) === 'from'
                || strtolower($name) === 'to'
                || strtolower($name) === 'subject'
                || strtolower($name) === 'reply-to'     // TODO: Remove once Relay bug-fix is deployed
            ) {
                continue;
            }
            $additionalHeaders[$header->getName()] = $header->getBodyAsString();
        }

        return $this->callApi(
            'POST',
            '/sends',
            SendEmailResponse::class,
            [
                "from" => [
                    "name" => $email->getFrom()[0]->getName(),
                    "email" => "testing@nadil.relay.hyvorstaging.com"
//                    "email" => $email->getFrom()[0]->getAddress()
                ],
                "to" => [
                    "name" => $email->getTo()[0]->getName(),
                    "email" => $email->getTo()[0]->getAddress()
//                    "email" => "98o1onday2@mrotzis.com"
                ],
                "subject" => $email->getSubject(),
                "body_html" => $email->getHtmlBody(),
                "body_text" => $email->getTextBody(),
                "headers" => $additionalHeaders,
            ]
        );
    }
}
