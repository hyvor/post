<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ProjectController;
use App\Entity\Factory\ProjectFactory;
use App\Entity\Project;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ProjectController::class, 'deleteProject')]
#[CoversMethod(ProjectService::class, 'deleteProject')]
class DeleteProjectTest extends WebTestCase
{

    // TODO: tests for input validation (when the project is not found)
    // TODO: tests for authentication
    public function testDeleteProjectFound(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create(fn (Project $project) => $project->setName('Valid Project Name'));

        $project_id = $project->getId();

        $response = $this->consoleApi('DELETE', '/projects/' . $project->getId());

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('Project deleted', $data['message']);

        $find_project = $this->consoleApi('GET', '/project/' . $project_id);
        $this->assertEquals(404, $find_project->getStatusCode());
    }

    public function testDeleteProjectNotFound(): void
    {
        $response = $this->consoleApi('DELETE', '/projects/1');

        $this->assertEquals(404, $response->getStatusCode());
    }
}
