<?php

namespace App\Service\Integration\Relay;

use App\Service\AppConfig;
use App\Service\Integration\Relay\Exception\RelayApiException;
use App\Service\Integration\Relay\Response\CreateDomainResponse;
use App\Service\Integration\Relay\Response\DeleteDomainResponse;
use App\Service\Integration\Relay\Response\SendEmailResponse;
use App\Service\Integration\Relay\Response\VerifyDomainResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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
        private SerializerInterface $serializer,
        private LoggerInterface     $logger,
    )
    {
    }

    /**
     * @template T of object
     * @param class-string<T> $classToDeserialize
     * @param array<string, mixed> $data
     * @param array<string, mixed> $headers
     * @param int[] $backoffSeconds
     * @return T
     * @throws RelayApiException
     */
    private function callApi(
        string $method,
        string $endpoint,
        string $classToDeserialize,
        array  $data = [],
        array  $headers = [],
        array  $backoffSeconds = self::BACKOFF,
        bool   $isSystemNotification = false
    )
    {
        $attempts = 0;

        while (true) {
            try {
                $response = $this->httpClient->request(
                    $method,
                    $this->appConfig->getRelayUrl() . '/api/console/' . ltrim($endpoint, '/'),
                    [
                        'max_redirects' => 3,
                        'headers' => array_merge(
                            [
                                'Authorization' => 'Bearer ' . ($isSystemNotification ? $this->appConfig->getNotificationRelayApiKey() : $this->appConfig->getRelayApiKey()),
                            ],
                            $headers
                        ),
                        'json' => $data,
                    ]
                );

                return $this->serializer->deserialize($response->getContent(), $classToDeserialize, 'json');

            } catch (TransportExceptionInterface|HttpExceptionInterface $e) {

                $this->logger->error(
                    'Relay API call failed',
                    [
                        'method' => $method,
                        'endpoint' => $endpoint,
                        'data' => $data,
                        'headers' => $headers,
                        'exception' => $e,
                    ]
                );

                $errorMessage = isset($response) ? $this->getErrorMessageFromResponse($response) : 'Unknown error';

                $attempts++;
                if ($attempts >= self::MAX_ATTEMPTS) {
                    throw new RelayApiException('Max attempts reached, last error: ' . $errorMessage);
                }

                if (
                    $e instanceof HttpExceptionInterface &&
                    $e->getCode() >= 300 &&
                    $e->getCode() < 500 &&
                    $e->getCode() !== 429
                ) {
                    throw new RelayApiException($errorMessage);
                }

                sleep($backoffSeconds[$attempts - 1]);
            }
        }
    }

    private function getErrorMessageFromResponse(ResponseInterface $response): string
    {
        $defaultError = 'Unknown error';
        try {
            $data = $response->toArray(false);
            return $data['message'] ?? $defaultError;
        } catch (DecodingExceptionInterface) {
            return $defaultError;
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
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
    public function verifyDomain(int $id): VerifyDomainResponse
    {
        return $this->callApi(
            'POST',
            "/domains/verify",
            VerifyDomainResponse::class,
            [
                'id' => $id
            ]
        );
    }

    /**
     * @throws RelayApiException
     */
    public function deleteDomain(string $domain): void
    {
        $this->callApi(
            'DELETE',
            '/domains',
            DeleteDomainResponse::class,
            [
                'domain' => $domain
            ]
        );
    }

    /**
     * @throws RelayApiException
     */
    public function sendEmail(
        Email $email,
        ?int  $idempotencyKey = null,
        bool  $isSystemNotification = false
    ): SendEmailResponse
    {
        $additionalHeaders = [];

        foreach ($email->getHeaders()->all() as $header) {
            $name = $header->getName();
            if (
                strtolower($name) === 'from'
                || strtolower($name) === 'to'
                || strtolower($name) === 'subject'
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
                    "email" => $email->getFrom()[0]->getAddress()
                ],
                "to" => [
                    "name" => $email->getTo()[0]->getName(),
                    "email" => $email->getTo()[0]->getAddress()
                ],
                "subject" => $email->getSubject(),
                "body_html" => $email->getHtmlBody(),
                "body_text" => $email->getTextBody(),
                "headers" => $additionalHeaders,
            ],
            [
                // TODO: remove prefix here
                'X-Idempotency-Key' => $idempotencyKey ? "newsletter-send-{$idempotencyKey}" : '',
            ],
            backoffSeconds: self::EMAIL_BACKOFF,
            isSystemNotification: $isSystemNotification
        );
    }
}
