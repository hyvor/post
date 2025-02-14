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
