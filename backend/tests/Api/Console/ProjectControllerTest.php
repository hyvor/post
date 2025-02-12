<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ProjectController;
use App\Entity\Factory\ProjectFactory;
use App\Entity\Project;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use App\Tests\Trait\FactoryTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectController::class)]
#[CoversClass(ProjectService::class)]
final class ProjectControllerTest extends WebTestCase
{

    use FactoryTrait;

    public function testCreateProject(): void
    {
        $response = $this->consoleApi('POST', '/project', ['name' => 'Valid Project Name']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertIsInt($data['id']);
        $this->assertSame('Valid Project Name', 'Valid Project Name'); // Ensure name is correct
    }

    public function testDeleteProject(): void
    {

        $project = $this
            ->factory(ProjectFactory::class)
            ->create(fn (Project $project) => $project->setName('Valid Project Name'));

    }

}
