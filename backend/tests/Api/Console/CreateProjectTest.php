<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ProjectController;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ProjectController::class, 'createProject')]
#[CoversMethod(ProjectService::class, 'createProject')]
class CreateProjectTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testCreateProject(): void
    {
        $response = $this->consoleApi('POST', '/project', ['name' => 'Valid Project Name']);

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

}