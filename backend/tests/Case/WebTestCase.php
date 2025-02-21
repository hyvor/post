<?php

namespace App\Tests\Case;

use App\Entity\Project;
use App\Tests\Trait\FactoryTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthFake;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    use FactoryTrait;

    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $container = static::getContainer();
        AuthFake::enableForSymfony($container, ['id' => 1]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
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
     * @return array<string, mixed>
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

}
