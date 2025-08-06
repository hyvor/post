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
    const int MAX_ATTEMPTS = 3;
    /** @var int[] $BACKOFF */
    const array BACKOFF = [1, 2, 5];
    /** @var int[] $EMAIL_BACKOFF */
    const array EMAIL_BACKOFF = [5, 15, 30];

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
     * @param array<string, mixed> $headers
     * @return T
     * @throws RelayApiException
     */
    private function callApi(
        string $method,
        string $endpoint,
        string $classToDeserialize,
        array  $data = [],
        array  $headers = [],
        bool   $isEmailSend = false
    )
    {
        $attempts = 0;

        while (true) {
            try {
                $response = $this->httpClient->request(
                    $method,
                    $this->appConfig->getRelayUrl() . '/api/console/' . ltrim($endpoint, '/'),
                    [
                        'headers' => array_merge(
                            [
                                'Authorization' => 'Bearer ' . $this->appConfig->getRelayApiKey(),
                            ],
                            $headers
                        ),
                        'json' => $data,
                    ]
                );

                $statusCode = $response->getStatusCode();

                if ($statusCode >= 200 && $statusCode < 300) {
                    return $this->serializer->deserialize($response->getContent(), $classToDeserialize, 'json');
                }

                $attempts++;
                $backoff = $isEmailSend ? self::EMAIL_BACKOFF : self::BACKOFF;
                $json = $response->toArray(false);

                if ($attempts >= self::MAX_ATTEMPTS) {
                    throw new RelayApiException($json['message'] ?? 'Unknown error');
                }

                if ($statusCode === 429) {
                    $waitTime = $response->getHeaders()['X-RateLimit-Reset'][0] ?? null;
                    $sleepTime = is_numeric($waitTime) ? (int)$waitTime : $backoff[$attempts - 1];
                    sleep($sleepTime);
                } elseif ($statusCode >= 500 && $statusCode < 600) {
                    sleep($backoff[$attempts - 1]);
                } else {
                    throw new RelayApiException($json['message'] ?? 'Unknown error');
                }

            } catch (TransportExceptionInterface|HttpExceptionInterface|DecodingExceptionInterface $e) {
                throw new RelayApiException($e->getMessage());
            }
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

    public function sendEmail(Email $email, ?int $idempotencyKey = null): SendEmailResponse
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
            ],
            [
                'X-Idempotency-Key' => $idempotencyKey ? "newsletter-send-{$idempotencyKey}" : '',
            ]
        );
    }
}
