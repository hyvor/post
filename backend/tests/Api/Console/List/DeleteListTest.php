<?php

namespace App\Tests\Api\Console\List;

use App\Api\Console\Controller\ListController;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Entity\NewsletterList;
use App\Service\NewsletterList\NewsletterListService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListController::class)]
#[CoversClass(NewsletterListService::class)]
class DeleteListTest extends WebTestCase
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
            ->create(fn ($newsletterList) => $newsletterList->setName('Valid List Name')->setProject($project));

        $newsletterListId = $newsletterList->getId();

        $response = $this->consoleApi(
            $project,
        'DELETE',
            '/lists/' . $newsletterList->getId()
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);

        $repository = $this->em->getRepository(NewsletterList::class);
        $list = $repository->find($newsletterListId);
        $this->assertNull($list);
    }

    public function testDeleteNewsletterListNotFound(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/lists/1'
        );

        $this->assertSame(404, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame('Entity not found', $data['message']);

    }

}
