<?php

namespace App\Tests\Api\Console\NewsletterList;

use App\Api\Console\Controller\NewsletterListController;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Entity\NewsletterList;
use App\Service\NewsletterList\NewsletterListService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterListController::class)]
#[CoversClass(NewsletterListService::class)]
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

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/lists/' . $newsletterList->getId(),
            [
                'name' => 'New Name',
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);

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

        $response = $this->consoleApi($project1,
            'PATCH', '/lists/' . $newsletterList->getId(),
            [
                'project_id' => $project2->getId()
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertSame($project2->getId(), $data['project_id']);

        $list_find = $this->em->getRepository(NewsletterList::class)->find($newsletterList->getId());
        $this->assertSame($project2->getId(), $list_find->getProject()->getId());
    }

}
