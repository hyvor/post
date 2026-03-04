<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Entity\Subscriber;
use App\Entity\SubscriberListRemoval;
use App\Entity\Type\ListRemovalReason;
use App\Entity\Type\SubscriberMetadataDefinitionType;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Service\App\Messenger\MessageTransport;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\ConfirmationMail\ConfirmationMailListener;
use App\Service\Subscriber\ConfirmationMail\SendConfirmationMailMessage;
use App\Service\Subscriber\Event\SubscriberCreatedEvent;
use App\Service\Subscriber\Event\SubscriberUpdatedEvent;
use App\Service\Subscriber\Event\SubscriberUpdatingEvent;
use App\Service\Subscriber\ListRemoval\ListRemovalListener;
use App\Service\Subscriber\ListRemoval\ListRemovalService;
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
#[CoversClass(ListRemovalService::class)]
#[CoversClass(CreateSubscriberInput::class)]
#[CoversClass(ConfirmationMailListener::class)]
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
                'subscribed_at' => null, // even if set
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

    public function test_validates_metadata_definition_exists(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => 'test@email.com',
            'metadata' => ['nonexistent-key' => 'value'],
        ]);

        $this->assertResponseFailed(422, 'nonexistent-key');
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

        $this->assertResponseIsSuccessful();

        $repo = $this->em->getRepository(SubscriberListRemoval::class);
        foreach ([$list1, $list2] as $list) {
            $record = $repo->findOneBy(['list' => $list, 'subscriber' => $subscriber]);
            $this->assertInstanceOf(SubscriberListRemoval::class, $record);
            $this->assertSame($reason, $record->getReason());
        }
    }

    public function test_updates_list_removal(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list],
        ]);

        $removal = SubscriberListRemovalFactory::createOne([
            'subscriber' => $subscriber,
            'list' => $list,
            'reason' => ListRemovalReason::OTHER,
        ]);

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'lists' => [],
            'lists_strategy' => 'overwrite',
            'list_removal_reason' => 'bounce',
        ]);

        $this->assertResponseIsSuccessful();

        $this->em->clear();

        $records = $this->em->getRepository(SubscriberListRemoval::class)->findAll();
        $this->assertCount(1, $records);
        $this->assertSame($removal->getId(), $records[0]->getId());
        $this->assertSame(ListRemovalReason::BOUNCE, $records[0]->getReason());
        $this->assertSame($subscriber->getId(), $records[0]->getSubscriber()->getId());
        $this->assertSame($list->getId(), $records[0]->getList()->getId());
    }

    public function test_list_removal_make_sure_adding_lists_is_not_recorded(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'lists' => [$list->getId()],
            'lists_strategy' => 'overwrite',
        ]);

        $this->assertResponseIsSuccessful();

        $records = $this->em->getRepository(SubscriberListRemoval::class)->findBy([
            'subscriber' => $subscriber,
        ]);
        $this->assertCount(0, $records);
    }

    public function test_list_removal_with_strategy_remove(): void
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
            'list_removal_reason' => 'other',
        ]);

        $this->assertResponseIsSuccessful();

        refresh($subscriber);
        $this->assertCount(1, $subscriber->getLists());
        $this->assertSame($list2->getId(), $subscriber->getLists()[0]?->getId());

        $record = $this->em->getRepository(SubscriberListRemoval::class)->findOneBy([
            'list' => $list1,
            'subscriber' => $subscriber,
        ]);
        $this->assertInstanceOf(SubscriberListRemoval::class, $record);
        $this->assertSame(ListRemovalReason::OTHER, $record->getReason());
    }

    #[TestWith([ListRemovalReason::UNSUBSCRIBE])]
    #[TestWith([ListRemovalReason::BOUNCE])]
    public function test_list_skips_if_previously_removed(ListRemovalReason $reason): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        SubscriberListRemovalFactory::createOne([
            'subscriber' => $subscriber,
            'list' => $list,
            'reason' => $reason,
        ]);

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'lists' => [$list->getId()],
        ]);

        $this->assertResponseIsSuccessful();

        refresh($subscriber);
        $this->assertCount(0, $subscriber->getLists());
    }

    public function test_list_does_not_skip_other(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        SubscriberListRemovalFactory::createOne([
            'subscriber' => $subscriber,
            'list' => $list,
            'reason' => ListRemovalReason::OTHER,
        ]);

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'lists' => [$list->getId()],
        ]);

        $this->assertResponseIsSuccessful();

        refresh($subscriber);
        $this->assertCount(1, $subscriber->getLists());
        $this->assertSame($list->getId(), $subscriber->getLists()[0]?->getId());
    }

    public function test_list_can_bypass_removal_and_removes_removal(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['newsletter' => $newsletter]);

        $removal = SubscriberListRemovalFactory::createOne([
            'subscriber' => $subscriber,
            'list' => $list,
            'reason' => ListRemovalReason::UNSUBSCRIBE,
        ]);
        $removalId = $removal->getId();

        $otherRemoval = SubscriberListRemovalFactory::createOne([
            'subscriber' => $subscriber,
            'list' => NewsletterListFactory::createOne(['newsletter' => $newsletter]),
            'reason' => ListRemovalReason::UNSUBSCRIBE,
        ]);

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'lists' => [$list->getId()],
            'list_skip_resubscribe_on' => [],
        ]);
        $this->em->clear();

        $this->assertResponseIsSuccessful();

        refresh($subscriber);
        $this->assertCount(1, $subscriber->getLists());
        $this->assertSame($list->getId(), $subscriber->getLists()[0]?->getId());

        $record = $this->em->getRepository(SubscriberListRemoval::class)->find($removalId);
        $this->assertNull($record);

        $recordOther = $this->em->getRepository(SubscriberListRemoval::class)->find($otherRemoval->getId());
        $this->assertInstanceOf(SubscriberListRemoval::class, $recordOther);
    }

    public function test_updates_with_confirmation_email_true(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subscriber = SubscriberFactory::createOne(
            ['newsletter' => $newsletter, 'status' => SubscriberStatus::SUBSCRIBED],
        );

        $this->consoleApi($newsletter, 'POST', '/subscribers', [
            'email' => $subscriber->getEmail(),
            'status' => 'pending',
            'send_pending_confirmation_email' => true,
        ]);

        $this->assertResponseIsSuccessful();

        $event = $this->getEd()->getFirstEvent(SubscriberUpdatedEvent::class);
        $this->assertTrue($event->shouldSendConfirmationEmail());

        $transport = $this->transport(MessageTransport::ASYNC);
        $transport->queue()->assertContains(SendConfirmationMailMessage::class);
        $message = $transport->queue()->messages(SendConfirmationMailMessage::class)[0];
        $this->assertSame($subscriber->getId(), $message->getSubscriberId());
    }

    #[TestWith([9999])]
    #[TestWith(['list'])]
    public function test_list_not_found(int|string $val): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
                'lists' => [$val],
            ],
        );

        $this->assertResponseFailed(
            422,
            'Lists with ' . (is_int($val) ? 'IDs' : 'names') . ' ' . $val . ' not found',
        );
    }

    /**
     * @param array<string, mixed> $input
     */
    #[TestWith(
        [
            [
                'email' => '',
            ],
            'email: This value should not be blank',
        ],
        'empty email'
    )]
    #[TestWith(
        [
            ['email' => 'not-an-email'],
            'email: This value is not a valid email address',
        ],
        'invalid email'
    )]
    #[TestWith(
        [
            ['email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@h.co'],
            'email: This value is too long',
        ],
        'too long email'
    )]
    #[TestWith(
        [
            ['email' => 'test@email.com', 'lists' => 'not-array'],
            'lists',
        ],
        'string for lists'
    )]
    #[TestWith(
        [
            ['email' => 'test@email.com', 'status' => 'invalid-status'],
            'status',
        ],
        'invalid status'
    )]
    #[TestWith(
        [
            ['email' => 'test@email.com', 'subscribe_ip' => 'not-an-ip'],
            'subscribe_ip: This value is not a valid IP address',
        ],
        'invalid IP address'
    )]
    #[TestWith(
        [
            ['email' => 'test@email.com', 'list_skip_resubscribe_on' => ['invalid-reason']],
            'not a valid choice',
        ],
        'invalid list_skip_resubscribe_on value'
    )]
    public function test_validation(array $input, string $message): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            $input,
        );

        $this->assertResponseFailed(
            422,
            $message,
        );
    }

    #[TestWith(['not a valid ip'])]
    #[TestWith(['127.0.0.1'])] // private ip
    #[TestWith(['::1'])] // localhost
    #[TestWith(['169.254.255.255'])] // reserved ip
    public function test_validates_ip(
        string $ip,
    ): void {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'supun@hyvor.com',
                'lists' => [],
                'subscribe_ip' => $ip,
            ],
        );

        $this->assertResponseFailed(
            422,
            'This value is not a valid IP address.',
        );
    }

}
