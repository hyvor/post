<?php

namespace App\Tests\Case;

use App\Entity\Project;
use App\Tests\Trait\FactoryTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    use FactoryTrait;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
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

        // TODO: add authentication here
        $this->client->request(
            $method,
            '/api/console' . $uri,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RESOURCE_ID' => $projectId,
            ],
            content: (string) json_encode($data)
        );
        return $this->client->getResponse();
    }

}