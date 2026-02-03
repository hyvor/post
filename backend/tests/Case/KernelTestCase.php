<?php

namespace App\Tests\Case;

use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Testing\BaseTestingTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    use BaseTestingTrait;

    protected Container $container;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $this->container->get(EntityManagerInterface::class);
        $this->em = $em;
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
}
