<?php

namespace Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
class CreateSubscriberTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testCreateSubscriberValid(): void
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

        $response = $this->consoleApi(
            $project,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
                'list_ids'=> [$newsletterList1->getId(), $newsletterList2->getId()]
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertIsInt($json['id']);
        $this->assertSame('test@email.com', $json['email']);

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($json['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertSame('test@email.com', $subscriber->getEmail());
    }

    public function testCreateSubscriberInvalidEmail(): void
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

        $not_email = str_repeat('a', 256);
        $response = $this->consoleApi(
            $project,
            'POST',
            '/subscribers',
            [
                'email' => $not_email,
                'list_ids'=> [$newsletterList1->getId(), $newsletterList2->getId()]
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
    }

    public function testCreateSubscriberInvalidList(): void
    {
        $project1 = $this
            ->factory(ProjectFactory::class)
            ->create();

        $project2 = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList1 = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project2));

        $response = $this->consoleApi(
            $project1,
            'POST',
            '/subscribers',
            [
                'list_ids'=> [$newsletterList1->getId()]
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
    }

}
