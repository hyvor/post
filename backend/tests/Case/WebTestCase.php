<?php

namespace App\Tests\Case;

use App\Entity\Newsletter;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthFake;
use Monolog\Handler\TestHandler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Hyvor\Internal\Bundle\Testing\ApiTestingTrait;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    use AllTestCaseTrait;
    use ApiTestingTrait;

    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $this->container = static::getContainer();
        AuthFake::enableForSymfony($this->container, ['id' => 1]);

        /** @var EntityManagerInterface $em */
        $em = $this->container->get(EntityManagerInterface::class);
        $this->em = $em;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $files
     * @param array<string, mixed> $parameters
     */
    public function consoleApi(
        Newsletter|int|null $newsletter,
        string $method,
        string $uri,
        array $data = [],
        array $files = [],
        // only use this if $files is used. otherwise, use $data
        array $parameters = [],
    ): Response {
        $newsletterId = $newsletter instanceof Newsletter ? $newsletter->getId() : $newsletter;

        $this->client->getCookieJar()->set(new Cookie('authsess', 'default'));
        $this->client->request(
            $method,
            '/api/console' . $uri,
            parameters: $parameters,
            files: $files,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_NEWSLETTER_ID' => $newsletterId,
            ],
            content: (string)json_encode($data),
        );
        return $this->client->getResponse();
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function publicApi(
        string $method,
        string $uri,
        array $data = [],
        array $headers = [],
    ): Response {
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

    public function getTestLogger(): TestHandler
    {
        $logger = $this->container->get('monolog.handler.test');
        $this->assertInstanceOf(TestHandler::class, $logger);
        return $logger;
    }


}
