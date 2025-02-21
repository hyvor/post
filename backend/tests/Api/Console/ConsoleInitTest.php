<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ConsoleController;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Entity\Project;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConsoleController::class)]
#[CoversClass(ProjectService::class)]
class ConsoleInitTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testInitConsole(): void
    {
        $projects = $this
            ->factory(ProjectFactory::class)
            ->createMany(10);

        $response = $this->consoleApi(
            null,
            'GET',
            '/init'
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('projects', $data);
        $this->assertIsArray($data['projects']);
        $this->assertSame(10, count($data['projects']));
    }

    public function testInitProject(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $response = $this->consoleApi(
            $project->getId(),
            'GET',
            '/init/project',
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('project', $data);
        $this->assertIsArray($data['project']);
        $this->assertSame($project->getId(), $data['project']['id']);
    }

    public function testInitProjectWithStats(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterLists = $this
            ->factory(NewsletterListFactory::class)
            ->createMany(
                10,
                fn ($newsletterList) => $newsletterList->setProject($project)
            );

        $response = $this->consoleApi(
            $project->getId(),
            'GET',
            '/init/project',
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('stats', $data);
        $this->assertIsArray($data['stats']);

        $stats = $data['stats'];
        $this->assertIsArray($stats['subscribers']);
        $this->assertIsArray($stats['issues']);
        $this->assertIsArray($stats['lists']);

        $subscibers = $stats['subscribers'];
        $this->assertArrayHasKey('total', $subscibers);
        $this->assertArrayHasKey('last_30d', $subscibers);
        $this->assertSame(10, $subscibers['total']);
        $this->assertSame(10, $subscibers['last_30d']);

    }
}
