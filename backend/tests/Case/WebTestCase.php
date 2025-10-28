<?php

namespace App\Tests\Case;

use App\Api\Console\Authorization\Scope;
use App\Entity\Newsletter;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Sudo\SudoUserFactory;
use Hyvor\Internal\Util\Crypt\Encryption;
use Monolog\Handler\TestHandler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpFoundation\Response;
use Hyvor\Internal\Bundle\Testing\ApiTestingTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    use AllTestCaseTrait;
    use ApiTestingTrait;

    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected Container $container;
    protected Encryption $encryption;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $this->container = static::getContainer();
        if ($this->shouldEnableAuthFake()) {
            AuthFake::enableForSymfony($this->container, ['id' => 1]);
        }
        /** @var EntityManagerInterface $em */
        $em = $this->container->get(EntityManagerInterface::class);
        $this->em = $em;

        $encryption = $this->container->get(Encryption::class);
        $this->assertInstanceOf(Encryption::class, $encryption);
        $this->encryption = $encryption;
    }

    protected function shouldEnableAuthFake(): bool
    {
        return true;
    }

    protected function mockRelayClient(?callable $callback = null): void
    {
        if (!$callback) {
            $callback = function ($method, $url, $options): JsonMockResponse {

                $this->assertSame('POST', $method);
                $this->assertStringStartsWith('https://relay.hyvor.com/api/console/', $url);
                $this->assertContains('Content-Type: application/json', $options['headers']);
                $this->assertContains('Authorization: Bearer test-relay-key', $options['headers']);

                return new JsonMockResponse();
            };
        }

        $httpClient = new MockHttpClient($callback);
        $this->container->set(HttpClientInterface::class, $httpClient);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $files
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $server
     * @param true|(string|Scope)[] $scopes
     */
    public function consoleApi(
        Newsletter|int|null $newsletter,
        string              $method,
        string              $uri,
        array               $data = [],
        array               $files = [],
        // only use this if $files is used. otherwise, use $data
        array               $parameters = [],
        array               $server = [],
        true|array          $scopes = true,
        bool                $useSession = false
    ): Response
    {
        if ($newsletter instanceof Newsletter) {
            $newsletterId = $newsletter->getId();
        } else if ($newsletter) {
            $newsletterId = $newsletter;
            $newsletter = NewsletterFactory::findOrCreate(['id' => $newsletterId]);
        }

        if ($useSession || $newsletter === null) {
            $this->client->getCookieJar()->set(new Cookie('authsess', 'test'));
            if ($newsletter) {
                UserFactory::findOrCreate([
                    'newsletter' => $newsletter,
                    'hyvor_user_id' => 1,
                ]);
                $server['HTTP_X_NEWSLETTER_ID'] = (string)$newsletterId;
            }
        } else {
            $apiKey = bin2hex(random_bytes(16));
            $apiKeyHashed = hash('sha256', $apiKey);
            $apiKeyFactory = ['key_hashed' => $apiKeyHashed, 'newsletter' => $newsletter];
            if ($scopes !== true) {
                $apiKeyFactory['scopes'] = array_map(
                    fn(Scope|string $scope) => is_string($scope) ? $scope : $scope->value,
                    $scopes
                );
            }
            ApiKeyFactory::createOne($apiKeyFactory);
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $apiKey;
        }
        $this->client->getCookieJar()->set(new Cookie('authsess', 'default'));
        $this->client->request(
            $method,
            '/api/console' . $uri,
            parameters: $parameters,
            files: $files,
            server: array_merge([
                'CONTENT_TYPE' => 'application/json',
            ], $server),
            content: (string)json_encode($data),
        );

        $response = $this->client->getResponse();

        if ($response->getStatusCode() === 500) {
            throw new \Exception(
                'API call failed with status code 500. ' .
                'Response: ' . $response->getContent()
            );
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function publicApi(
        string $method,
        string $uri,
        array  $data = [],
        array  $headers = [],
    ): Response
    {
        $server = [
            'CONTENT_TYPE' => 'application/json',
        ];

        foreach ($headers as $key => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }

        $this->client->request(
            $method,
            '/api/public' . $uri,
            server: $server,
            content: (string)json_encode($data)
        );
        return $this->client->getResponse();
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $server
     */
    public function sudoApi(
        string $method,
        string $uri,
        array  $data = [],
        array  $server = [],
    ): Response
    {
        SudoUserFactory::findOrCreate([
            'user_id' => 1
        ]);

        $this->client->getCookieJar()->set(new Cookie('authsess', 'test-session'));

        $this->client->request(
            $method,
            '/api/sudo' . $uri,
            server: array_merge([
                'CONTENT_TYPE' => 'application/json',
            ], $server),
            content: (string)json_encode($data),
        );

        $response = $this->client->getResponse();

        if ($response->getStatusCode() === 500) {
            throw new \Exception(
                'API call failed with status code 500. ' .
                'Response: ' . $response->getContent()
            );
        }

        return $response;
    }

    public function getTestLogger(): TestHandler
    {
        $logger = $this->container->get('monolog.handler.test');
        $this->assertInstanceOf(TestHandler::class, $logger);
        return $logger;
    }

    public function assertApiFailed(int $expectedStatus, string $expectedMessage): void
    {

        $response = $this->client->getResponse();
        $this->assertSame($expectedStatus, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertArrayHasKey('message', $json);
        $this->assertIsString($json['message']);
        $this->assertStringContainsString($expectedMessage, $json['message']);

    }


}
