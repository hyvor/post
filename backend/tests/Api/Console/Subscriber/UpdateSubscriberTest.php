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
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
class UpdateSubscriberTest extends WebTestCase
{
    use ClockSensitiveTrait;

    public function testUpdateStatus(): void
    {
        static::mockTime(new \DateTimeImmutable('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();
        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::UNSUBSCRIBED,
            'opt_in_at' => new \DateTimeImmutable('2025-01-01'),
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'status' => 'subscribed',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertSame('subscribed', $subscriber->getStatus()->value);
        $this->assertSame('2025-02-21 00:00:00', $subscriber->getUpdatedAt()->format('Y-m-d H:i:s'));
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

    public function testCannotUpdateSubscriberOfOtherNewsletter(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter1,
            'email' => 'ishini@hyvor.com',
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

        SubscriberMetadataDefinitionFactory::createOne([
            'key' => 'name',
            'name' => 'Name',
            'newsletter' => $newsletter,
        ]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
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
                'metadata' => $metaUpdate,
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

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::UNSUBSCRIBED,
        ]);

        SubscriberMetadataDefinitionFactory::createOne([
            'key' => 'age',
            'name' => 'Age',
            'newsletter' => $newsletter,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/subscribers/' . $subscriber->getId(),
            [
                'metadata' => ['name' => 'Thibault'],
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
