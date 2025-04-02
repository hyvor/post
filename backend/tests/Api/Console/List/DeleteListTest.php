<?php

namespace App\Tests\Api\Console\List;

use App\Api\Console\Controller\ListController;
use App\Entity\NewsletterList;
use App\Service\NewsletterList\NewsletterListService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(ListController::class)]
#[CoversClass(NewsletterListService::class)]
#[CoversClass(NewsletterList::class)]
class DeleteListTest extends WebTestCase
{

    // TODO: tests for input validation (when the project is not found)
    // TODO: tests for authentication

    public function testDeleteNewsletterListFound(): void
    {
        Clock::set(new MockClock('2025-02-21'));

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
        $this->assertSame('2025-02-21 00:00:00', $list->getDeletedAt()->format('Y-m-d H:i:s'));
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
