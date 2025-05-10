<?php

namespace App\Tests\Api\Console\Project;

use App\Api\Console\Controller\ProjectController;
use App\Api\Console\Input\Project\UpdateProjectInput;
use App\Entity\Meta\ProjectMeta;
use App\Entity\Project;
use App\Entity\Type\UserRole;
use App\Service\Project\Dto\UpdateProjectMetaDto;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(ProjectController::class)]
#[CoversClass(ProjectService::class)]
#[CoversClass(UpdateProjectMetaDto::class)]
#[CoversClass(UpdateProjectInput::class)]
class UpdateProjectTest extends WebTestCase
{

    public function test_update_project_meta(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $meta = new ProjectMeta();
        $meta->template_logo = 'https://example.com/logo.png';
        $project = ProjectFactory::createOne([
            'meta' => $meta
        ]);

        $user = UserFactory::createOne([
            'project' => $project,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/projects',
            [
                'template_color_accent' => '#ff0000',
                'template_box_radius' => '10px',
                'template_logo' => null,
                'form_title' => 'Subscribe to newsletter'
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('#ff0000', $json['template_color_accent']);
        $this->assertSame('10px', $json['template_box_radius']);
        $this->assertNull($json['template_logo']);

        $repository = $this->em->getRepository(Project::class);
        $project = $repository->find($json['id']);

        $this->assertNotNull($project);
        $this->assertSame('2025-02-21 00:00:00', $project->getUpdatedAt()?->format('Y-m-d H:i:s'));
        $projectMeta = $project->getMeta();
        $this->assertInstanceOf(ProjectMeta::class, $projectMeta);
        $this->assertSame('#ff0000', $projectMeta->template_color_accent);
        $this->assertSame('10px', $projectMeta->template_box_radius);
        $this->assertSame(null, $projectMeta->template_logo);
        $this->assertSame('Subscribe to newsletter', $projectMeta->form_title);
    }
}
