<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ConsoleController;
use App\Api\Console\Object\ProjectListObject;
use App\Api\Console\Object\StatCategoryObject;
use App\Api\Console\Object\StatsObject;
use App\Entity\Type\UserRole;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConsoleController::class)]
#[CoversClass(ProjectService::class)]
#[CoversClass(StatsObject::class)]
#[CoversClass(StatCategoryObject::class)]
#[CoversClass(ProjectListObject::class)]
class ConsoleInitTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testInitConsole(): void
    {
        $projects = ProjectFactory::createMany(10, [
            'user_id' => 1,
        ]);

        foreach ($projects as $project) {
            UserFactory::createOne([
                'project' => $project,
                'hyvor_user_id' => 1,
                'role' => UserRole::OWNER
            ]);
        }

        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof \Doctrine\Bundle\DoctrineBundle\Registry);
        $doctrine->getManager()->clear();


        // other user
        ProjectFactory::createMany(1, [
            'user_id' => 2,
        ]);

        $projectAdmin = ProjectFactory::createOne([
            'user_id' => 1
        ]);

        // admin
        $user = UserFactory::createOne([
            'project' => $projectAdmin,
            'hyvor_user_id' => 1,
            'role' => UserRole::ADMIN
        ]);

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
        $this->assertSame(11, count($data['projects']));

        $this->assertArrayHasKey('config', $data);
        $config = $data['config'];
        $this->assertArrayHasKey('template_defaults', $config);
    }

    public function testInitProject(): void
    {
        $project = ProjectFactory::createOne();

        $user = UserFactory::createOne([
            'project' => $project,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

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
        $project = ProjectFactory::createOne();

        $user = UserFactory::createOne([
            'project' => $project,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

        NewsletterListFactory::createMany(10, [
            'project' => $project,
            'created_at' => new \DateTimeImmutable()
        ]);

        $otherProject = ProjectFactory::createOne();
        NewsletterListFactory::createMany(5, [
            'project' => $otherProject,
            'created_at' => new \DateTimeImmutable()
        ]);

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

        $lists = $stats['lists'];
        $this->assertArrayHasKey('total', $lists);
        $this->assertArrayHasKey('last_30d', $lists);
        $this->assertSame(10, $lists['total']);
        $this->assertSame(10, $lists['last_30d']);

    }

    public function testInitProjectWithLists(): void
    {
        $project = ProjectFactory::createOne();

        $user = UserFactory::createOne([
            'project' => $project,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

        $newsletterList = NewsletterListFactory::createOne([
            'project' => $project,
        ]);

        $subscribersOld = SubscriberFactory::createMany(5, [
            'project' => $project,
            'lists' => [$newsletterList],
            'created_at' => new \DateTimeImmutable('2021-01-01'),
        ]);

        $subscribersNew = SubscriberFactory::createMany(5, [
            'project' => $project,
            'lists' => [$newsletterList],
            'created_at' => new \DateTimeImmutable(),
        ]);

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
        $this->assertArrayHasKey('lists', $data);
        $this->assertIsArray($data['lists']);
        $this->assertSame(1, count($data['lists']));
        $list = $data['lists'][0];
        $this->assertIsArray($list);
        $this->assertArrayHasKey('id', $list);
        $this->assertArrayHasKey('name', $list);
        $this->assertSame($newsletterList->getId(), $list['id']);
        $this->assertSame($newsletterList->getName(), $list['name']);
        $this->assertSame(10, $list['subscribers_count']);
        $this->assertSame(5, $list['subscribers_count_last_30d']);
    }
}
