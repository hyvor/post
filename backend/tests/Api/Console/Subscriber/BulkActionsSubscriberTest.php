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
        $subscribers = $subscriberRepository->findAll();
        $this->assertCount(0, $subscribers, 'All subscribers should be deleted after bulk delete action.');
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

        $subscribers = $this->em->getRepository(Subscriber::class)
            ->createQueryBuilder('s')
            ->where('s.status != :status')
            ->setParameter('status', SubscriberStatus::UNSUBSCRIBED->value)
            ->getQuery()
            ->getResult();

        $this->assertCount(0, $subscribers, 'All subscribers should be unsubscribed after bulk status update action.');
    }

    public function test_bulk_status_update_status_not_provided(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscribers = SubscriberFactory::createMany(10, [
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
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Status must be provided for status change action', (string) $response->getContent());
    }

    public function test_bulk_status_update_invalid_status(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscribers = SubscriberFactory::createMany(10, [
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
                'status' => 'invalid_status',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Invalid status provided', (string) $response->getContent());
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

    public function test_bulk_metadata_update_metadata_not_provided(): void
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
                'action' => 'metadata_update',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Metadata must be provided for metadata update action', (string) $response->getContent());
    }

    public function test_bulk_metadata_update_metadata_not_found(): void
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
                'action' => 'metadata_update',
                'metadata' => [
                    'non_existent_key' => 'value',
                ],
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Metadata definition with key non_existent_key not found', (string) $response->getContent());
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
