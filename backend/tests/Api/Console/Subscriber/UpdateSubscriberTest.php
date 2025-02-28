<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Entity\Factory\SubscriberFactory;
use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
class UpdateSubscriberTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testUpdateList(): void
    {

        Clock::set(new MockClock('2025-02-21'));

        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList1 = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project));

        $newsletterList2 = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project));

        $subscriber = $this
            ->factory(SubscriberFactory::class)
            ->create(fn ($subscriber) => $subscriber
                ->setProject($project)
                ->addList($newsletterList1));

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'email' => 'new@email.com',
                'list_ids' => [$newsletterList1->getId(), $newsletterList2->getId()],
                'status' => 'subscribed',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('new@email.com', $json['email']);

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($json['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertSame('new@email.com', $subscriber->getEmail());
        $this->assertSame('subscribed', $subscriber->getStatus()->value);
        $this->assertCount(2, $subscriber->getLists());
        $this->assertContains($newsletterList1, $subscriber->getLists());
        $this->assertContains($newsletterList2, $subscriber->getLists());
        $this->assertSame('2025-02-21 00:00:00', $subscriber->getUpdatedAt()->format('Y-m-d H:i:s'));
    }

    public function testUpdateSubscriberEmptyList(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project));

        $subscriber = $this
            ->factory(SubscriberFactory::class)
            ->create(fn ($subscriber) => $subscriber
                ->setProject($project)
                ->addList($newsletterList));

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'list_ids' => [],
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateSubscriberInvalidStatus(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project));

        $subscriber = $this
            ->factory(SubscriberFactory::class)
            ->create(fn ($subscriber) => $subscriber
                ->setProject($project)
                ->addList($newsletterList));

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'status' => 'invalid',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateSubscriberInvalidEmail(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project));

        $subscriber = $this
            ->factory(SubscriberFactory::class)
            ->create(fn ($subscriber) => $subscriber
                ->setProject($project)
                ->addList($newsletterList));

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'email' => 'invalid',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
    }

    public function testUpdateSubscriberInvalidListId(): void
    {
        $project1 = $this
            ->factory(ProjectFactory::class)
            ->create();

        $project2 = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project2));

        $subscriber = $this
            ->factory(SubscriberFactory::class)
            ->create(fn ($subscriber) => $subscriber
                ->setProject($project1)
                ->addList($newsletterList));

        $response = $this->consoleApi(
            $project1,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'list_ids' => [$newsletterList->getId()],
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
    }
}
