<?php

namespace App\Tests\Api\Console\Project;

use App\Api\Console\Controller\ProjectController;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectController::class)]
#[CoversClass(ProjectService::class)]
#[CoversClass(ProjectRepository::class)]
#[CoversClass(Project::class)]
class CreateProjectTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testCreateProjectValid(): void
    {
        $response = $this->consoleApi(
            null,
            'POST',
            '/projects',
            [
                'name' => 'Valid Project Name'
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $project_id = $data['id'];
        $this->assertIsInt($data['id']);
        $this->assertSame('Valid Project Name', 'Valid Project Name');

        $repository = $this->em->getRepository(Project::class);
        $find = $repository->find($project_id);
        $this->assertNotNull($find);
        $this->assertSame('Valid Project Name', $find->getName());
    }

    public function testCreateProjectInvalid(): void
    {
        $long_string = str_repeat('a', 256);
        $response = $this->consoleApi(
            null,
            'POST', '/projects',
            [
                'name' => $long_string
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertSame('This value is too long. It should have 255 characters or less.', $data['message']);

    }

}
