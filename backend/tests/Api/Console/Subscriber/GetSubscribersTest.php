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

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
class GetSubscribersTest extends WebTestCase
{

    // TODO: tests for authentication

    public function testListSubscribersNonEmpty(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList1 = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project));

        $newsletterList2 = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project));

        $subscribers = $this
            ->factory(SubscriberFactory::class)
            ->createMany(
                5,
                function ($subscriber) use ($project, $newsletterList1, $newsletterList2) {
                    $subscriber->setProject($project);
                    $subscriber->addList($newsletterList1);
                    $subscriber->addList($newsletterList2);
                }
            );

        $projectOther = $this
            ->factory(ProjectFactory::class)
            ->create();

        $this
            ->factory(SubscriberFactory::class)
            ->createMany(
                2,
                function ($subscriber) use ($projectOther) {
                    $subscriber->setProject($projectOther);
                }
            );

        $response = $this->consoleApi(
            $project,
            'GET',
            '/subscribers'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(5, $json);

        $subscriber = $json[0];
        $this->assertIsArray($subscriber);
        $this->assertArrayHasKey('id', $subscriber);
        $this->assertArrayHasKey('email', $subscriber);
    }


    public function testListSubscribersPagination(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $subscribers = $this
            ->factory(SubscriberFactory::class)
            ->createMany(
                5,
                function ($subscriber) use ($project) {
                    $subscriber->setProject($project);
                }
            );

        $response = $this->consoleApi(
            $project,
            'GET',
            '/subscribers?limit=2&offset=1'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(2, $json);

        $subscriber = $json[0];
        $this->assertIsArray($subscriber);
        $this->assertArrayHasKey('id', $subscriber);
        $this->assertArrayHasKey('email', $subscriber);
    }

    public function testListSubscribersEmpty(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $response = $this->consoleApi(
            $project,
            'GET',
            '/subscribers'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(0, $json);
    }
}
