<?php
/*
namespace App\Tests\Api\Console\Project;

use App\Api\Console\Controller\ProjectController;
use App\Api\Console\Object\ProjectObject;
use App\Entity\Project;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\UserFactory;
use App\Entity\Type\UserRole;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectController::class)]
#[CoversClass(ProjectService::class)]
#[CoversClass(Project::class)]
#[CoversClass(ProjectObject::class)]
class GetProjectsTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testListProjectsEmpty(): void
    {
        $response = $this->consoleApi(
            null,
            'GET',
            '/projects'
        );

        $this->assertSame(200, $response->getStatusCode());

        $data = $this->getJson();
        $this->assertCount(0, $data);
    }

    public function testListProjectsNonEmpty(): void
    {
        $projects = ProjectFactory::createMany(10, ['user_id' => 1]);
        foreach ($projects as $project) {
            $user = UserFactory::createOne([
                'project' => $project,
                'hyvor_user_id' => 1,
                'role' => UserRole::OWNER
            ]);
        }

        $response = $this->consoleApi(
            null,
            'GET',
            '/projects'
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame(10, count($data));
    }
}*/
