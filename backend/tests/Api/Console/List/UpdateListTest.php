<?php

namespace App\Tests\Api\Console\List;

use App\Api\Console\Controller\ListController;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Entity\NewsletterList;
use App\Service\NewsletterList\NewsletterListService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(ListController::class)]
#[CoversClass(NewsletterListService::class)]
class UpdateListTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testUpdateListName(): void
    {

        Clock::set(new MockClock('2025-02-21'));

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

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);

        $this->assertSame('New Name', $data['name']);

        $this->em->getRepository(NewsletterList::class)->find($newsletterList->getId());
        $this->assertSame('New Name', $newsletterList->getName());
        $this->assertSame('2025-02-21 00:00:00', $newsletterList->getUpdatedAt()->format('Y-m-d H:i:s'));

    }

    public function testUpdateListNameInvalid(): void
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
                'name' => str_repeat('a', 256),
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('This value is too long. It should have 255 characters or less.', $data['message']);
    }

}
