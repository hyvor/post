<?php

namespace App\Tests\Api\Console\List;

use App\Api\Console\Controller\ListController;
use App\Entity\NewsletterList;
use App\Service\NewsletterList\NewsletterListService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListController::class)]
#[CoversClass(NewsletterListService::class)]
class DeleteListTest extends WebTestCase
{

    // TODO: tests for input validation (when the project is not found)
    // TODO: tests for authentication

    public function testDeleteNewsletterListFound(): void
    {
        $project = ProjectFactory::createOne();

        $newsletterList = NewsletterListFactory::createOne([
            'project' => $project
        ]);

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
        $project = ProjectFactory::createOne();

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
