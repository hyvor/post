<?php

namespace Api\Console\Project;

use App\Api\Console\Controller\ProjectController;
use App\Entity\Factory\ProjectFactory;
use App\Entity\Project;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectController::class)]
#[CoversClass(ProjectService::class)]
class GetProjectsTest extends WebTestCase
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

    public function testListProjectsNonEmpty(): void
    {
        $projects = $this
            ->factory(ProjectFactory::class)
            ->createMany(10);

        $response = $this->consoleApi('GET', '/projects');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertEquals(10, count($data));
    }

    public function testGetSpecificProjet(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create(fn (Project $project) => $project->setName('Valid Project Name'));

        $response = $this->consoleApi('GET', '/projects/' . $project->getId());

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertEquals($project->getId(), $data['id']);
        $this->assertEquals($project->getName(), $data['name']);
    }
}
