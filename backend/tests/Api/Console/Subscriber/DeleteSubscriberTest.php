<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
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
        $project = ProjectFactory::createOne();
        $newsletterList = NewsletterListFactory::createOne(['project' => $project]);

        $subscriber = SubscriberFactory::createOne([
            'project' => $project,
            'lists' => [$newsletterList],
        ]);

        $subscriberId = $subscriber->getId();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/subscribers/' . $subscriber->getId()
        );

        $this->assertSame(200, $response->getStatusCode());

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($subscriberId);
        $this->assertNull($subscriber);
    }

    public function testDeleteSubscriberNotFound(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/subscribers/1'
        );

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testCannotDeleteOtherProjectSubscriber(): void
    {
        $project = ProjectFactory::createOne();
        $otherProject = ProjectFactory::createOne();

        $newsletterList = NewsletterListFactory::createOne(['project' => $project]);

        $subscriber = SubscriberFactory::createOne([
            'project' => $project,
            'lists' => [$newsletterList],
        ]);

        $response = $this->consoleApi(
            $otherProject,
            'DELETE',
            '/subscribers/' . $subscriber->getId()
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Entity does not belong to the project', $this->getJson($response)['message']);
    }
}
