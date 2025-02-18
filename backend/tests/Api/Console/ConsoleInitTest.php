<?php

namespace Api\Console;

use App\Api\Console\Controller\ConsoleController;
use App\Entity\Factory\ProjectFactory;
use App\Entity\Project;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConsoleController::class)]
#[CoversClass(ProjectService::class)]
class ConsoleInitTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testInitConsole(): void
    {
        $projects = $this
            ->factory(ProjectFactory::class)
            ->createMany(10);

        $response = $this->consoleApi(
            null,
            'GET',
            '/init'
        );

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('projects', $data);
        $this->assertIsArray($data['projects']);
        $this->assertEquals(10, count($data['projects']));
    }
}
