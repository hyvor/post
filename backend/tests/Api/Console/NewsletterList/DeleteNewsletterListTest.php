<?php

namespace App\Tests\Api\Console\NewsletterList;

use App\Api\Console\Controller\NewsletterListController;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Service\NewsletterList\NewsletterListService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterListController::class)]
#[CoversClass(NewsletterListService::class)]
class DeleteNewsletterListTest extends WebTestCase
{

    // TODO: tests for input validation (when the project is not found)
    // TODO: tests for authentication
    public function testDeleteNewsletterListFound(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setName('Valid List Name')
                ->setProject($project));

        $newsletterList_id = $newsletterList->getId();

        $response = $this->consoleApi(
            $project,
        'DELETE',
            '/lists/' . $newsletterList->getId()
        );

        // TODO: calling two HTTP endpoints in the same test is not recommended

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('List deleted', $data['message']);

        $find_list = $this->consoleApi(
            $project,
            'GET',
            '/lists/' . $newsletterList_id
        );
        $this->assertEquals(404, $find_list->getStatusCode());
    }

    public function testDeleteNewsletterListNotFound(): void
    {
        $response = $this->consoleApi(
            null,
            'DELETE',
            '/lists/1'
        );

        $this->assertEquals(404, $response->getStatusCode());
    }
}
