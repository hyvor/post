<?php

namespace App\Tests\Api\Console\List;

use App\Api\Console\Controller\ListController;
use App\Entity\Factory\ProjectFactory;
use App\Entity\Project;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListController::class)]
#[CoversClass(ListController::class)]
#[CoversClass(ListController::class)]
class CreateListTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testCreateNewsLetterListValid(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create(fn (Project $project) => $project->setName('Valid Project Name'));

        $response = $this->consoleApi(
            $project,
            'POST',
            '/lists',
            [
                'name' => 'Valid List Name'
            ],
        );

        $content = $response->getContent();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertIsInt($data['id']);
        $this->assertSame('Valid List Name', $data['name']);
        $this->assertSame($project->getId(), $data['project_id']);
    }

    public function testCreateProjectInvalid(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create(fn (Project $project) => $project->setName('Valid Project Name'));

        $long_string = str_repeat('a', 256);
        $response = $this->consoleApi(
            $project,
            'POST',
            '/lists',
            [
                'name' => $long_string, 'project_id' => $project->getId()
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
    }

}
