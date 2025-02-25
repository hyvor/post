<?php

namespace Api\Console\Subscriber;

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
class GetSubscriberTest extends WebTestCase
{

    // TODO: tests for input validation
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
                10,
                function ($subscriber) use ($project, $newsletterList1, $newsletterList2) {
                    $subscriber->setProject($project);
                    $subscriber->addList($newsletterList1);
                    $subscriber->addList($newsletterList2);
                }
            );

        $response = $this->consoleApi(
            $project,
            'GET',
            '/subscribers'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(10, $json);
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
