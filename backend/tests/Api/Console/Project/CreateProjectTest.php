<?php

namespace App\Tests\Api\Console\Project;

use App\Api\Console\Controller\ProjectController;
use App\Entity\NewsletterList;
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

        $json = $this->getJson($response);
        $projectId = $json['id'];
        $this->assertIsInt($projectId);

        $repository = $this->em->getRepository(Project::class);
        $project = $repository->find($projectId);
        $this->assertNotNull($project);
        $this->assertSame('Valid Project Name', $project->getName());

        $listRepository = $this->em->getRepository(NewsletterList::class);
        $lists = $listRepository->findBy(['project' => $project]);
        $this->assertCount(1, $lists);
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
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertHasViolation($data, 'name', 'This value is too long. It should have 255 characters or less.');
    }

}
