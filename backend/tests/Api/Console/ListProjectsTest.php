<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ProjectController;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ProjectController::class, 'listProjects')]
#[CoversMethod(ProjectService::class, 'listProjects')]
class ListProjectsTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testListProjectsEmpty(): void
    {
        $response = $this->consoleApi('GET', '/projects');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertEquals(0, count($data));
    }

}
