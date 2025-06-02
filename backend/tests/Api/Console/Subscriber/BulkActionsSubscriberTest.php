<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
class BulkActionsSubscriberTest extends WebTestCase
{
    public function test_bulk_delete_subscribers(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscribers = SubscriberFactory::createMany(50, [
            'newsletter' => $newsletter,
        ]);

        $subscriberIds = array_map(fn(Subscriber $subscriber) => $subscriber->getId(), $subscribers);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/bulk',
            [
                'subscribers_ids' => $subscriberIds,
                'action' => 'delete',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Subscribers deleted successfully', (string) $response->getContent());

        $subscriberRepository = $this->em->getRepository(Subscriber::class);
        foreach ($subscriberIds as $id) {
            $subscriber = $subscriberRepository->find($id);
            $this->assertNull($subscriber, "Subscriber with ID $id should be deleted.");
        }
    }

    public function test_bulk_status_update(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscribers = SubscriberFactory::createMany(50, [
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $subscriberIds = array_map(fn(Subscriber $subscriber) => $subscriber->getId(), $subscribers);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/bulk',
            [
                'subscribers_ids' => $subscriberIds,
                'action' => 'status_change',
                'status' => 'unsubscribed',
            ]
        );


        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Subscribers status updated successfully', (string) $response->getContent());

        $subscriberRepository = $this->em->getRepository(Subscriber::class);
        foreach ($subscriberIds as $id) {
            $subscriber = $subscriberRepository->find($id);
            $this->assertNotNull($subscriber, "Subscriber with ID $id should exist after status change.");
            $this->assertSame('unsubscribed', $subscriber->getStatus()->value, "Subscriber with ID $id should be unsubscribed.");
        }
    }

    public function test_bulk_metadata_update(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscribers = SubscriberFactory::createMany(50, [
            'newsletter' => $newsletter,
        ]);

        $metadataDef = SubscriberMetadataDefinitionFactory::createOne([
            'newsletter' => $newsletter,
            'key' => 'source',
        ]);

        SubscriberMetadataDefinitionFactory::createOne([
            'newsletter' => $newsletter,
            'key' => 'campaign',
        ]);

        $subscriberIds = array_map(fn(Subscriber $subscriber) => $subscriber->getId(), $subscribers);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/bulk',
            [
                'subscribers_ids' => $subscriberIds,
                'action' => 'metadata_update',
                'metadata' => [
                    'source' => 'test_source',
                    'campaign' => 'test_campaign',
                ],
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Subscribers metadata updated successfully', (string) $response->getContent());

        $subscriberRepository = $this->em->getRepository(Subscriber::class);
        foreach ($subscriberIds as $id) {
            $subscriber = $subscriberRepository->find($id);
            $this->assertNotNull($subscriber, "Subscriber with ID $id should exist after metadata update.");
            $this->assertSame('test_source', $subscriber->getMetadata()['source'] ?? null, "Subscriber with ID $id should have updated source metadata.");
            $this->assertSame('test_campaign', $subscriber->getMetadata()['campaign'] ?? null, "Subscriber with ID $id should have updated campaign metadata.");
        }
    }

    public function test_bulk_action_invalid_subscriber_ids(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/bulk',
            [
                'subscribers_ids' => [9999, 8888], // Non-existent IDs
                'action' => 'delete',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Subscriber with ID 9999 not found in the newsletter', (string) $response->getContent());
    }

    public function test_bulk_action_exceeds_limit(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscribers = SubscriberFactory::createMany(100, [
            'newsletter' => $newsletter,
        ]);

        $subscriberIds = array_map(fn(Subscriber $subscriber) => $subscriber->getId(), $subscribers);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/bulk',
            [
                'subscribers_ids' => $subscriberIds,
                'action' => 'delete',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Subscribers limit exceeded', (string) $response->getContent());
    }

    public function test_bulk_action_invalid_action(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscribers = SubscriberFactory::createMany(10, [
            'newsletter' => $newsletter,
        ]);

        $subscriberIds = array_map(fn(Subscriber $subscriber) => $subscriber->getId(), $subscribers);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/bulk',
            [
                'subscribers_ids' => $subscriberIds,
                'action' => 'invalid_action',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Invalid action.', (string) $response->getContent());
    }
}
