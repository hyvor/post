<?php

namespace Api\Console\Project;

use App\Api\Console\Controller\ProjectController;
use App\Entity\Factory\ProjectFactory;
use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectController::class)]
#[CoversClass(ProjectService::class)]
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

        $response = $this->consoleApi(
            $project,
            'DELETE', '/projects'
        );

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('Project deleted', $data['message']);

        $repository = $this->em->getRepository(Project::class);
        $find = $repository->find($project_id);
        $this->assertNull($find);
    }

    public function testDeleteProjectNotFound(): void
    {
        $response = $this->consoleApi(
            null,
            'DELETE',
            '/projects'
        );

        $this->assertEquals(400, $response->getStatusCode());
    }
}
