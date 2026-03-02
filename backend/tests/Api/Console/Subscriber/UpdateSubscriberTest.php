<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Newsletter;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
class UpdateSubscriberTest extends WebTestCase
{
    use ClockSensitiveTrait;

    // TODO: tests for authentication

    public function testUpdateList(): void
    {
        static::mockTime(new \DateTimeImmutable('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list1],
            'status' => SubscriberStatus::UNSUBSCRIBED,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'email' => 'new@email.com',
                'list_ids' => [$list1->getId(), $list2->getId()],
                'status' => 'subscribed',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
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
            fn(Newsletter $newsletter) => [
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
            fn(Newsletter $newsletter) => [
                'status' => 'invalid',
            ],
            [
                [
                    'property' => 'status',
                    'message' => 'This value should be of type int|string.',
                ],
            ]
        );
    }

    /**
     * @param callable(Newsletter): array<string, mixed> $input
     * @param array<int, array{property: string, message: string}> $violations
     * @return void
     */
    private function validateInput(
        callable $input,
        array    $violations
    ): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            $input($newsletter),
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertHasViolation($violations[0]['property'], $violations[0]['message']);
    }

    public function testUpdateSubscriberInvalidListId(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();

        $newsletterList = NewsletterListFactory::createOne(['newsletter' => $newsletter2]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter1]);

        $response = $this->consoleApi(
            $newsletter1,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'list_ids' => [$newsletterList->getId()],
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertSame(
            'List with id ' . $newsletterList->getId() . ' not found',
            $json['message']
        );
    }

    public function testCannotUpdateSubscriberOfOtherNewsletter(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();

        $newsletterList = NewsletterListFactory::createOne(['newsletter' => $newsletter1]);
        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter1,
            'email' => 'ishini@hyvor.com',
            'lists' => [$newsletterList],
        ]);

        $response = $this->consoleApi(
            $newsletter2,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'email' => 'supun@hyvor.com',
            ]
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Entity does not belong to the newsletter', $this->getJson()['message']);

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($subscriber->getId());
        $this->assertSame('ishini@hyvor.com', $subscriber?->getEmail());
    }

    public function testUpdateSubscriberWithTakenEmail(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subscriber1 = SubscriberFactory::createOne(['newsletter' => $newsletter, 'email' => 'thibault@hyvor.com']);
        $subscriber2 = SubscriberFactory::createOne(['newsletter' => $newsletter, 'email' => 'supun@hyvor.com']);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/subscribers/' . $subscriber1->getId(),
            [
                'email' => 'supun@hyvor.com',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(
            'Subscriber with email ' . $subscriber2->getEmail() . ' already exists',
            $this->getJson()['message']
        );
    }

    public function test_update_subscriber_metadata(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $metadata = SubscriberMetadataDefinitionFactory::createOne([
            'key' => 'name',
            'name' => 'Name',
            'newsletter' => $newsletter,
        ]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list1],
            'status' => SubscriberStatus::UNSUBSCRIBED,
        ]);

        $metaUpdate = [
            'name' => 'Thibault',
        ];

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'email' => 'new@email.com',
                'list_ids' => [$list1->getId(), $list2->getId()],
                'status' => 'subscribed',
                'metadata' => $metaUpdate
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $subscriber = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertSame($subscriber->getMetadata(), $metaUpdate);
    }

    public function test_update_subscriber_metadata_invalid_name(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list1],
            'status' => SubscriberStatus::UNSUBSCRIBED,
        ]);

        $metadata = SubscriberMetadataDefinitionFactory::createOne([
            'key' => 'age',
            'name' => 'Age',
            'newsletter' => $newsletter,
        ]);


        $metaUpdate = [
            'name' => 'Thibault',
        ];

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'metadata' => $metaUpdate,
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame(
            'Metadata definition with key name not found',
            $json['message']
        );
    }


    public function test_update_subscriber_metadata_invalid_type(): void
    {
        // TODO: Implement this test when other metadata types are implemented
        $this->markTestSkipped();
    }
}
