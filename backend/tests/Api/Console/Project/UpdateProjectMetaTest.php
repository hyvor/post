<?php

namespace App\Tests\Api\Console\Project;

use App\Api\Console\Controller\ProjectController;
use App\Api\Console\Input\Project\UpdateProjectMetaInput;
use App\Entity\Meta\ProjectMeta;
use App\Entity\Project;
use App\Service\Project\Dto\UpdateProjectMetaDto;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(ProjectController::class)]
#[CoversClass(ProjectService::class)]
#[CoversClass(UpdateProjectMetaDto::class)]
#[CoversClass(UpdateProjectMetaInput::class)]
class UpdateProjectMetaTest extends WebTestCase
{

    public function test_update_project_meta(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/projects',
            [
                'template_color_accent' => '#ff0000',
                'template_box_radius' => '10px',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('#ff0000', $json['templateColorAccent']);
        $this->assertSame('10px', $json['templateBoxRadius']);

        $repository = $this->em->getRepository(Project::class);
        $project = $repository->find($json['id']);

        $this->assertSame('2025-02-21 00:00:00', $project->getUpdatedAt()->format('Y-m-d H:i:s'));
        $this->assertNotNull($project);
        $projectMeta = $project->getMeta();
        $this->assertInstanceOf(ProjectMeta::class, $projectMeta);
        $this->assertSame('#ff0000', $projectMeta->templateColorAccent);
    }
}
