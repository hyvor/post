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

        // TODO: database check

    }

}
