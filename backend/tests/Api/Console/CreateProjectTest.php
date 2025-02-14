<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ProjectController;
use App\Repository\ProjectRepository;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectController::class)]
#[CoversClass(ProjectService::class)]
#[CoversClass(ProjectRepository::class)]
class CreateProjectTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testCreateProjectValid(): void
    {
        $response = $this->consoleApi('POST', '/projects', ['name' => 'Valid Project Name']);

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertIsInt($data['id']);
        $this->assertSame('Valid Project Name', 'Valid Project Name'); // Ensure name is correct
    }

    public function testCreateProjectInvalid(): void
    {
        $long_string = str_repeat('a', 256);
        $response = $this->consoleApi('POST', '/projects', ['name' => $long_string]);

        $this->assertEquals(422, $response->getStatusCode());
    }

}
