<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Newsletter;
use App\Entity\Subscriber;
use App\Entity\SubscriberListRemoval;
use App\Entity\Type\ListRemovalReason;
use App\Entity\Type\SubscriberMetadataDefinitionType;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\Event\SubscriberCreatedEvent;
use App\Service\Subscriber\Event\SubscriberUpdatedEvent;
use App\Service\Subscriber\Event\SubscriberUpdatingEvent;
use App\Service\Subscriber\ListRemoval\ListRemovalListener;
use App\Service\Subscriber\Message\SubscriberCreatedMessage;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\SubscriberListRemovalFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberCreatedEvent::class)]
#[CoversClass(SubscriberUpdatingEvent::class)]
#[CoversClass(SubscriberUpdatedEvent::class)]
#[CoversClass(NewsletterListService::class)]
#[CoversClass(ListRemovalListener::class)]
class CreateSubscriberTest extends WebTestCase
{

    use ClockSensitiveTrait;

    public function test_create_subscriber_minimal(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
            'name' => 'List 1',
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
                'lists' => [
                    'List 1',
                ],
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertIsInt($json['id']);
        $this->assertSame('test@email.com', $json['email']);

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($json['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertSame('test@email.com', $subscriber->getEmail());
        $this->assertSame(SubscriberStatus::SUBSCRIBED, $subscriber->getStatus());
        $this->assertSame('console', $subscriber->getSource()->value);

        $lists = $subscriber->getLists();
        $this->assertCount(1, $lists);
        $this->assertSame('List 1', $lists[0]?->getName());

        $event = $this->getEd()->getFirstEvent(SubscriberCreatedEvent::class);
        $this->assertSame($json['id'], $event->getSubscriber()->getId());
        $this->assertFalse($event->shouldSendConfirmationEmail());
    }

    public function test_create_subscriber_with_all_inputs(): void
    {
        $this->getEd()->setMockEvents([SubscriberCreatedEvent::class]);

        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'Named List']);

        $subscribedAt = new \DateTimeImmutable('2023-06-15 10:00:00');

        SubscriberMetadataDefinitionFactory::createOne([
            'newsletter' => $newsletter,
            'key' => 'test-key',
            'type' => SubscriberMetadataDefinitionType::TEXT,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'test@hyvor.com',
                'lists' => [$list1->getId(), 'Named List'],
                'status' => 'pending',
                'source' => 'import',
                'subscribe_ip' => '203.0.113.1',
                'subscribed_at' => $subscribedAt->getTimestamp(),
                'metadata' => [
                    'test-key' => 'test',
                ],
                'send_pending_confirmation_email' => true,
            ],
        );
        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertIsInt($json['id']);
        $this->assertSame('test@hyvor.com', $json['email']);
        $this->assertSame('pending', $json['status']);
        $this->assertSame('import', $json['source']);
        $this->assertSame('203.0.113.1', $json['subscribe_ip']);
        $this->assertSame($subscribedAt->getTimestamp(), $json['subscribed_at']);
        $this->assertCount(2, $json['list_ids']);
        $this->assertContains($list1->getId(), $json['list_ids']);
        $this->assertContains($list2->getId(), $json['list_ids']);

        $this->em->clear();
        $subscriber = $this->em->getRepository(Subscriber::class)->find($json['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertSame('test@hyvor.com', $subscriber->getEmail());
        $this->assertSame(SubscriberStatus::PENDING, $subscriber->getStatus());
        $this->assertSame(SubscriberSource::IMPORT, $subscriber->getSource());
        $this->assertSame('203.0.113.1', $subscriber->getSubscribeIp());
        $this->assertSame('2023-06-15 10:00:00', $subscriber->getSubscribedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame(
            [
                'test-key' => 'test',
            ],
            $subscriber->getMetadata(),
        );
        $listIds = $subscriber->getLists()->map(fn($l) => $l->getId())->toArray();
        $this->assertCount(2, $listIds);
        $this->assertContains($list1->getId(), $listIds);
        $this->assertContains($list2->getId(), $listIds);

        $event = $this->getEd()->getFirstEvent(SubscriberCreatedEvent::class);
        $this->assertSame($json['id'], $event->getSubscriber()->getId());
        $this->assertTrue($event->shouldSendConfirmationEmail());
    }


    public function test_creates_subscriber_fills_subscribed_at(): void
    {
        $this->mockTime('2026-01-01');

        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
            ],
        );

        $subscriber = $this->em->getRepository(Subscriber::class)->find($this->getJson()['id']);
        $this->assertNotNull($subscriber);
        $this->assertSame(SubscriberStatus::SUBSCRIBED, $subscriber->getStatus());
        $this->assertSame('2026-01-01', $subscriber->getSubscribedAt()?->format('Y-m-d'));
    }

    public function test_updates_subscriber_all(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        SubscriberMetadataDefinitionFactory::createOne([
            'newsletter' => $newsletter,
            'key' => 'a',
        ]);

        $subscriber = SubscriberFactory::createOne([
            'email' => 'supun@hyvor.com',
            'newsletter' => $newsletter,
            'lists' => [
                $list1,
            ],
            'status' => SubscriberStatus::PENDING,
            'source' => SubscriberSource::FORM,
            'subscribe_ip' => '1.2.3.4',
            'subscribed_at' => new \DateTimeImmutable('2026-01-02'),
        ]);

        $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'supun@hyvor.com',
                'lists' => [$list2->getId()], // merge
                'status' => 'subscribed',
                'source' => 'console',
                'subscribe_ip' => '2.3.4.5',
                'subscribed_at' => new \DateTimeImmutable('2026-01-01')->getTimestamp(),
                'metadata' => [
                    'a' => 'b',
                ],
            ],
        );

        $this->assertResponseIsSuccessful();

        refresh($subscriber);

        $this->assertSame(SubscriberStatus::SUBSCRIBED, $subscriber->getStatus());
        $this->assertSame(SubscriberSource::CONSOLE, $subscriber->getSource());
        $this->assertSame('2.3.4.5', $subscriber->getSubscribeIp());
        $this->assertSame('2026-01-01', $subscriber->getSubscribedAt()?->format('Y-m-d'));
        $this->assertSame([
            'a' => 'b',
        ], $subscriber->getMetadata());

        $lists = $subscriber->getLists();
        $this->assertCount(2, $lists);

        $listIds = $lists->map(fn($l) => $l->getId())->toArray();
        $this->assertContains($list1->getId(), $listIds);
        $this->assertContains($list2->getId(), $listIds);
    }

    public function test_lists_strategy_overwrite(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list1],
        ]);

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'lists' => [$list2->getId()],
            'lists_strategy' => 'overwrite',
        ]);

        $this->assertResponseIsSuccessful();

        refresh($subscriber);
        $listIds = $subscriber->getLists()->map(fn($l) => $l->getId())->toArray();
        $this->assertCount(1, $listIds);
        $this->assertContains($list2->getId(), $listIds);
    }

    public function test_lists_strategy_remove(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list1, $list2],
        ]);

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'lists' => [$list1->getId()],
            'lists_strategy' => 'remove',
        ]);

        $this->assertResponseIsSuccessful();

        refresh($subscriber);
        $listIds = $subscriber->getLists()->map(fn($l) => $l->getId())->toArray();
        $this->assertCount(1, $listIds);
        $this->assertContains($list2->getId(), $listIds);
    }

    public function test_metadata_strategy_merge(): void
    {
        $newsletter = NewsletterFactory::createOne();

        foreach (['a', 'b', 'c'] as $key) {
            SubscriberMetadataDefinitionFactory::createOne(['newsletter' => $newsletter, 'key' => $key]);
        }

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'metadata' => ['a' => '1', 'b' => '2'],
        ]);

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'metadata' => ['b' => 'updated', 'c' => '3'],
            'metadata_strategy' => 'merge',
        ]);

        $this->assertResponseIsSuccessful();

        refresh($subscriber);
        $this->assertSame(['a' => '1', 'b' => 'updated', 'c' => '3'], $subscriber->getMetadata());
    }

    public function test_metadata_strategy_overwrite(): void
    {
        $newsletter = NewsletterFactory::createOne();

        foreach (['a', 'b', 'c'] as $key) {
            SubscriberMetadataDefinitionFactory::createOne(['newsletter' => $newsletter, 'key' => $key]);
        }

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'metadata' => ['a' => '1', 'b' => '2'],
        ]);

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'metadata' => ['c' => '3'],
            'metadata_strategy' => 'overwrite',
        ]);

        $this->assertResponseIsSuccessful();

        refresh($subscriber);
        $this->assertSame(['c' => '3'], $subscriber->getMetadata());
    }

    #[TestWith([ListRemovalReason::UNSUBSCRIBE])]
    #[TestWith([ListRemovalReason::BOUNCE])]
    public function test_records_list_removal(ListRemovalReason $reason): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list1, $list2],
        ]);

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'lists' => [],
            'lists_strategy' => 'overwrite',
            'list_removal_reason' => $reason->value,
        ]);
        // make sure the records are recorded
    }

    public function test_updates_list_removal(): void
    {
        // test ON CONFLICT
    }

    public function test_list_removal_make_sure_adding_lists_is_not_recorded(): void {}

    public function test_list_removal_with_strategy_remove(): void {}

    public function testCreateSubscriberWithListsById(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
                'lists' => [$list1->getId(), $list2->getId()],
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $this->em->clear();
        $json = $this->getJson();
        $subscriber = $this->em->getRepository(Subscriber::class)->find($json['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertCount(2, $subscriber->getLists());
        $listIds = $subscriber->getLists()->map(fn($l) => $l->getId())->toArray();
        $this->assertContains($list1->getId(), $listIds);
        $this->assertContains($list2->getId(), $listIds);
    }

    public function testCreateSubscriberWithListsByName(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'My Newsletter']);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
                'lists' => ['My Newsletter'],
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $this->em->clear();
        $json = $this->getJson();
        $subscriber = $this->em->getRepository(Subscriber::class)->find($json['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertCount(1, $subscriber->getLists());
        $this->assertSame($list->getId(), $subscriber->getLists()->first()->getId());
    }

    public function testCreateSubscriberWithAllInputs(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscribedAt = new \DateTimeImmutable('2021-08-27 12:00:00');
        $unsubscribedAt = new \DateTimeImmutable('2021-08-29 12:00:00');

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'supun@hyvor.com',
                'source' => 'form',
                'subscribe_ip' => '79.255.1.1',
                'subscribed_at' => $subscribedAt->getTimestamp(),
                'unsubscribed_at' => $unsubscribedAt->getTimestamp(),
                'lists' => [$list->getId()],
                'list_add_strategy_if_unsubscribed' => 'force_add',
                'list_remove_reason' => 'other',
                'send_pending_confirmation_email' => false,
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertIsInt($json['id']);
        $this->assertSame('supun@hyvor.com', $json['email']);
        $this->assertSame(SubscriberStatus::PENDING->value, $json['status']);
        $this->assertSame('form', $json['source']);
        $this->assertSame('79.255.1.1', $json['subscribe_ip']);
        $this->assertSame($subscribedAt->getTimestamp(), $json['subscribed_at']);
        $this->assertSame($unsubscribedAt->getTimestamp(), $json['unsubscribed_at']);

        $this->em->clear();
        $subscriber = $this->em->getRepository(Subscriber::class)->find($json['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertSame('supun@hyvor.com', $subscriber->getEmail());
        $this->assertSame(SubscriberStatus::PENDING, $subscriber->getStatus());
        $this->assertSame(SubscriberSource::FORM, $subscriber->getSource());
        $this->assertSame('79.255.1.1', $subscriber->getSubscribeIp());
        $this->assertSame('2021-08-27 12:00:00', $subscriber->getSubscribedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-08-29 12:00:00', $subscriber->getUnsubscribedAt()?->format('Y-m-d H:i:s'));
        $this->assertCount(1, $subscriber->getLists());
    }

    public function testUpdateExistingSubscriber(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'email' => 'supun@hyvor.com',
            'status' => SubscriberStatus::UNSUBSCRIBED,
            'subscribe_ip' => '1.2.3.4',
        ]);

        $subscribedAt = new \DateTimeImmutable('2024-01-01 00:00:00');

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'supun@hyvor.com',
                'status' => 'pending',
                'subscribe_ip' => '79.255.1.1',
                'subscribed_at' => $subscribedAt->getTimestamp(),
                'source' => 'import',
                'lists' => [$list->getId()],
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $this->em->clear();
        $updated = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $updated);
        $this->assertSame(SubscriberStatus::PENDING, $updated->getStatus());
        $this->assertSame('79.255.1.1', $updated->getSubscribeIp());
        $this->assertSame('2024-01-01 00:00:00', $updated->getSubscribedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame(SubscriberSource::IMPORT, $updated->getSource());
        $this->assertCount(1, $updated->getLists());
        $this->assertSame($list->getId(), $updated->getLists()->first()->getId());
    }

    public function testListAddStrategyIgnore(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        SubscriberListRemovalFactory::createOne([
            'list' => $list,
            'subscriber' => $subscriber,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => $subscriber->getEmail(),
                'lists' => [$list->getId()],
                'list_add_strategy_if_unsubscribed' => 'ignore',
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $this->em->clear();
        $updated = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $updated);
        // Subscriber should NOT be added to the list (was previously unsubscribed, strategy=ignore)
        $this->assertCount(0, $updated->getLists());
    }

    public function testListAddStrategyForceAdd(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        SubscriberListRemovalFactory::createOne([
            'list' => $list,
            'subscriber' => $subscriber,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => $subscriber->getEmail(),
                'lists' => [$list->getId()],
                'list_add_strategy_if_unsubscribed' => 'force_add',
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $this->em->clear();
        $updated = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $updated);
        // Subscriber SHOULD be added even though previously unsubscribed
        $this->assertCount(1, $updated->getLists());
        $this->assertSame($list->getId(), $updated->getLists()->first()->getId());
    }

    public function testListRemoveReasonUnsubscribe(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter, 'lists' => [$list]]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => $subscriber->getEmail(),
                'lists' => [], // empty = remove from all lists
                'list_remove_reason' => 'unsubscribe',
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $this->em->clear();
        $updated = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $updated);
        $this->assertCount(0, $updated->getLists());

        // Should record unsubscription
        $record = $this->em->getRepository(SubscriberListRemoval::class)->findOneBy([
            'list' => $list->_real(),
            'subscriber' => $updated,
        ]);
        $this->assertInstanceOf(SubscriberListRemoval::class, $record);
    }

    public function testListRemoveReasonOther(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter, 'lists' => [$list]]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => $subscriber->getEmail(),
                'lists' => [], // empty = remove from all lists
                'list_remove_reason' => 'other',
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $this->em->clear();
        $updated = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $updated);
        $this->assertCount(0, $updated->getLists());

        // Should NOT record unsubscription
        $record = $this->em->getRepository(SubscriberListRemoval::class)->findOneBy([
            'list' => $list->_real(),
            'subscriber' => $updated,
        ]);
        $this->assertNull($record);
    }

    public function testSendPendingConfirmationEmail(): void
    {
        $this->mockRelayClient();
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
                'lists' => [],
                'send_pending_confirmation_email' => true,
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $transport = $this->transport('async');
        $transport->queue()->assertCount(1);
        $message = $transport->queue()->first()->getMessage();
        $this->assertInstanceOf(SubscriberCreatedMessage::class, $message);
    }

    public function testNoConfirmationEmailByDefault(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
                'lists' => [],
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $transport = $this->transport('async');
        $transport->queue()->assertCount(0);
    }

    public function testListNotFound(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
                'lists' => [999999],
            ],
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('List not found', $this->getJson()['message']);
    }

    public function testInputValidationEmptyEmail(): void
    {
        $this->validateInput(
            fn(Newsletter $newsletter) => ['lists' => []],
            [
                [
                    'property' => 'email',
                    'message' => 'This value should not be blank.',
                ],
            ],
        );
    }

    public function testInputValidationInvalidEmail(): void
    {
        $this->validateInput(
            fn(Newsletter $newsletter)
                => [
                'email' => 'not-email',
                'lists' => [],
            ],
            [
                [
                    'property' => 'email',
                    'message' => 'This value is not a valid email address.',
                ],
            ],
        );
    }

    public function testInputValidationEmailTooLong(): void
    {
        $this->validateInput(
            fn(Newsletter $newsletter)
                => [
                'email' => str_repeat('a', 256) . '@hyvor.com',
                'lists' => [],
            ],
            [
                [
                    'property' => 'email',
                    'message' => 'This value is too long. It should have 255 characters or less.',
                ],
            ],
        );
    }

    #[TestWith(['not a valid ip'])]
    #[TestWith(['127.0.0.1'])] // private ip
    #[TestWith(['::1'])] // localhost
    #[TestWith(['169.254.255.255'])] // reserved ip
    public function testValidatesIp(
        string $ip,
    ): void {
        $this->validateInput(
            fn(Newsletter $newsletter)
                => [
                'email' => 'supun@hyvor.com',
                'lists' => [],
                'subscribe_ip' => $ip,
            ],
            [
                [
                    'property' => 'subscribe_ip',
                    'message' => 'This value is not a valid IP address.',
                ],
            ],
        );
    }

    /**
     * @param callable(Newsletter): array<string, mixed> $input
     * @param array<int, array{property: string, message: string}> $violations
     * @return void
     */
    private function validateInput(
        callable $input,
        array $violations,
    ): void {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            $input($newsletter),
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertHasViolation($violations[0]['property'], $violations[0]['message']);
    }

}
