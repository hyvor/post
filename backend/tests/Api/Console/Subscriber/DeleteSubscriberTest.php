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
class DeleteSubscriberTest extends WebTestCase
{

    // TODO: tests for input validation (when the project is not found)
    // TODO: tests for authentication

    public function testDeleteSubscriberFound(): void
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

        $subscriber_id = $subscriber->getId();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/subscribers/' . $subscriber->getId()
        );

        $this->assertSame(200, $response->getStatusCode());

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($subscriber_id);
        $this->assertNull($subscriber);
    }

    public function testDeleteSubscriberNotFound(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/subscribers/1'
        );

        $this->assertSame(404, $response->getStatusCode());
    }
}
