<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Project;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
class UpdateSubscriberTest extends WebTestCase
{

    // TODO: tests for authentication

    public function testUpdateList(): void
    {

        Clock::set(new MockClock('2025-02-21'));

        $project = ProjectFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['project' => $project]);
        $list2 = NewsletterListFactory::createOne(['project' => $project]);

        $subscriber = SubscriberFactory::createOne([
            'project' => $project,
            'lists' => [$list1],
            'status' => SubscriberStatus::UNSUBSCRIBED,
        ]);

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'email' => 'new@email.com',
                'list_ids' => [$list1->getId(), $list2->getId()],
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
        $this->assertContains($list1->_real(), $subscriber->getLists());
        $this->assertContains($list2->_real(), $subscriber->getLists());
        $this->assertSame('2025-02-21 00:00:00', $subscriber->getUpdatedAt()->format('Y-m-d H:i:s'));
    }

    public function testCannotUpdateSubscriberToEmptyList(): void
    {

        $this->validateInput(
            fn (Project $project) => [
                'email' => 'mybademail',
                'list_ids' => [],
            ],
            [
                [
                    'property' => 'email',
                    'message' => 'This value is not a valid email address.',
                ],
                [
                    'property' => 'list_ids',
                    'message' => 'There should be at least one list.',
                ],
            ]
        );
    }

    public function testValidatesStatus(): void
    {
        $this->validateInput(
            fn (Project $project) => [
                'status' => 'invalid',
            ],
            [
                [
                    'property' => 'status',
                    'message' => 'This value should be of type subscribed|unsubscribed|pending.',
                ],
            ]
        );
    }

    /**
     * @param callable(Project): array<string, mixed> $input
     * @param array<mixed> $violations
     * @return void
     */
    private function validateInput(
        callable $input,
        array $violations
    ): void
    {
        $project = ProjectFactory::createOne();
        $subscriber = SubscriberFactory::createOne(['project' => $project]);

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            $input($project),
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame($violations, $json['violations']);
        $this->assertSame('Validation failed with ' . count($violations) . ' violations(s)', $json['message']);
    }

    public function testUpdateSubscriberInvalidListId(): void
    {
        $project1 = ProjectFactory::createOne();
        $project2 = ProjectFactory::createOne();

        $newsletterList = NewsletterListFactory::createOne(['project' => $project2]);
        $subscriber = SubscriberFactory::createOne(['project' => $project1]);

        $response = $this->consoleApi(
            $project1,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'list_ids' => [$newsletterList->getId()],
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame(
            'List with id ' . $newsletterList->getId() . ' not found',
            $json['message']
        );
    }

    public function testCannotUpdateSubscriberOfOtherProject(): void
    {
        $project1 = ProjectFactory::createOne();
        $project2 = ProjectFactory::createOne();

        $newsletterList = NewsletterListFactory::createOne(['project' => $project1]);
        $subscriber = SubscriberFactory::createOne([
            'project' => $project1,
            'email' => 'ishini@hyvor.com',
            'lists' => [$newsletterList],
        ]);

        $response = $this->consoleApi(
            $project2,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'email' => 'supun@hyvor.com',
            ]
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Entity does not belong to the project', $this->getJson($response)['message']);

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($subscriber->getId());
        $this->assertSame('ishini@hyvor.com', $subscriber?->getEmail());
    }
}
