<?php

namespace Api\Console\NewsletterList;

use App\Api\Console\Controller\NewsletterListController;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterListController::class)]
#[CoversClass(NewsletterListController::class)]
class UpdateNewsletterListTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testUpdateListName(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project));

        $response = $this->consoleApi('PATCH', '/lists/' . $newsletterList->getId(), [
            'name' => 'New Name',
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);

        $this->assertSame('New Name', $data['name']);

        // Get the list from the database
        $response = $this->consoleApi('GET', '/lists/' . $newsletterList->getId());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $data = json_decode($content, true);
        $this->assertSame('New Name', $data['name']);
    }

    public function testUpdateListProject(): void
    {
        $project1 = $this
            ->factory(ProjectFactory::class)
            ->create();

        $project2 = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project1));

        $response = $this->consoleApi('PATCH', '/lists/' . $newsletterList->getId(), [
            'project_id' => $project2->getId()
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertSame($project2->getId(), $data['project_id']);

        // Get the list from the database
        $response = $this->consoleApi('GET', '/lists/' . $newsletterList->getId());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $data = json_decode($content, true);
        $this->assertSame($project2->getId(), $data['project_id']);
    }

}
