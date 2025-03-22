<?php

namespace App\Tests\Case;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthFake;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    use AllTestCaseTrait;

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
     */
    public function consoleApi(
        Project|int|null $project,
        string $method,
        string $uri,
        array $data = [],
    ): Response
    {
        $projectId = $project instanceof Project ? $project->getId() : $project;

        $this->client->getCookieJar()->set(new Cookie('authsess', 'default'));
        $this->client->request(
            $method,
            '/api/console' . $uri,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_PROJECT_ID' => $projectId,
            ],
            content: (string) json_encode($data)
        );
        return $this->client->getResponse();
    }

    /**
     * @return array<mixed>
     */
    public function getJson(Response $response): array
    {
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $json = json_decode($content, true);
        $this->assertIsArray($json);
        return $json;
    }

    /**
     * @param array<mixed>|Response $response
     */
    public function assertHasViolation(array|Response $response, string $property, string $message = ''): void
    {

        if ($response instanceof Response) {
            $response = $this->getJson($response);
        }

        $this->assertArrayHasKey('violations', $response);
        $this->assertIsArray($response['violations']);

        $found = false;
        foreach ($response['violations'] as $violation) {
            $this->assertIsArray($violation);
            if ($violation['property'] === $property) {
                $found = true;
                if ($message) {
                    $this->assertStringContainsString($message, $violation['message']);
                }
            }
        }

        $this->assertTrue($found, 'Violation not found');

    }



}
