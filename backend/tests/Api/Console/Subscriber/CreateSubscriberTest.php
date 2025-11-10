<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Newsletter;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Repository\SubscriberRepository;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(SubscriberRepository::class)]
#[CoversClass(Subscriber::class)]
class CreateSubscriberTest extends WebTestCase
{

    // TODO: tests for authentication

    public function testCreateSubscriberMinimal(): void
    {
        $this->mockRelayClient();
        $newsletter = NewsletterFactory::createOne();

        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
                'list_ids' => [$list1->getId(), $list2->getId()]
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertIsInt($json['id']);
        $this->assertSame('test@email.com', $json['email']);

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($json['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertSame('test@email.com', $subscriber->getEmail());
        $this->assertSame(SubscriberStatus::PENDING, $subscriber->getStatus());
        $this->assertSame('console', $subscriber->getSource()->value);

        $subscriberLists = $subscriber->getLists();
        $this->assertCount(2, $subscriberLists);
        $this->assertSame($list1->getId(), $subscriberLists[0]?->getId());
        $this->assertSame($list2->getId(), $subscriberLists[1]?->getId());
    }

    public function testCreateSubscriberWithAllInputs(): void
    {
        $this->mockRelayClient();
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
                'list_ids' => [$list->getId()],
                'source' => 'form',
                'subscribe_ip' => '79.255.1.1',
                'subscribed_at' => $subscribedAt->getTimestamp(),
                'unsubscribed_at' => $unsubscribedAt->getTimestamp(),
            ]
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

        $repository = $this->em->getRepository(Subscriber::class);
        $subscriber = $repository->find($json['id']);
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertSame('supun@hyvor.com', $subscriber->getEmail());
        $this->assertSame(SubscriberStatus::PENDING, $subscriber->getStatus());
        $this->assertSame(SubscriberSource::FORM, $subscriber->getSource());
        $this->assertSame('79.255.1.1', $subscriber->getSubscribeIp());
        $this->assertSame('2021-08-27 12:00:00', $subscriber->getSubscribedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-08-29 12:00:00', $subscriber->getUnsubscribedAt()?->format('Y-m-d H:i:s'));
    }

    public function testInputValidationEmptyEmailAndListIds(): void
    {
        $this->validateInput(
            fn(Newsletter $newsletter) => [],
            [
                [
                    'property' => 'email',
                    'message' => 'This value should not be blank.',
                ],
                [
                    'property' => 'list_ids',
                    'message' => 'This value should not be blank.',
                ]
            ]
        );
    }

    public function testInputValidationInvalidEmailAndListIds(): void
    {
        $this->validateInput(
            fn(Newsletter $newsletter) => [
                'email' => 'not-email',
                'list_ids' => [
                    null,
                    1,
                    'string',
                ],
            ],
            [
                [
                    'property' => 'email',
                    'message' => 'This value is not a valid email address.',
                ],
                [
                    'property' => 'list_ids[0]',
                    'message' => 'This value should not be blank.',
                ],
                [
                    'property' => 'list_ids[2]',
                    'message' => 'This value should be of type int.',
                ],
            ]
        );
    }

    public function testInputValidationEmailTooLong(): void
    {
        $this->validateInput(
            fn(Newsletter $newsletter) => [
                'email' => str_repeat('a', 256) . '@hyvor.com',
                'list_ids' => [1],
            ],
            [
                [
                    'property' => 'email',
                    'message' => 'This value is too long. It should have 255 characters or less.',
                ],
            ]
        );
    }

    public function testInputValidationOptionalValues(): void
    {
        $this->validateInput(
            fn(Newsletter $newsletter) => [
                'email' => 'supun@hyvor.com',
                'list_ids' => [1],
                'source' => 'invalid-source',
                'subscribe_ip' => '127.0.0.1',
                'subscribed_at' => 'invalid-date',
                'unsubscribed_at' => 'invalid-date',
            ],
            [
                [
                    'property' => 'source',
                    'message' => 'This value should be of type console|form|import.',
                ],
                [
                    'property' => 'subscribed_at',
                    'message' => 'This value should be of type int|null.',
                ],
                [
                    'property' => 'unsubscribed_at',
                    'message' => 'This value should be of type int|null.',
                ],
            ]
        );
    }

    #[TestWith(['not a valid ip'])]
    #[TestWith(['127.0.0.1'])] // private ip
    #[TestWith(['::1'])] // localhost
    #[TestWith(['169.254.255.255'])] // reserved ip
    public function testValidatesIp(
        string $ip
    ): void
    {
        $this->validateInput(
            fn(Newsletter $newsletter) => [
                'email' => 'supun@hyvor.com',
                'list_ids' => [1],
                'subscribe_ip' => $ip,
            ],
            [
                [
                    'property' => 'subscribe_ip',
                    'message' => 'This value is not a valid IP address.',
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

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            $input($newsletter),
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame($violations, $json['violations']);
        $this->assertSame($violations[0]['message'], $json['message']);
    }

    public function testCreateSubscriberInvalidList(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();

        $newsletterList1 = NewsletterListFactory::createOne(['newsletter' => $newsletter2]);

        $response = $this->consoleApi(
            $newsletter1,
            'POST',
            '/subscribers',
            [
                'email' => 'supun@hyvor.com',
                'list_ids' => [$newsletterList1->getId()]
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('List with id ' . $newsletterList1->getId() . ' not found', $this->getJson()['message']);
    }

    public function testCreateSubscriberDuplicateEmail(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(
            [
                'newsletter' => $newsletter,
                'email' => 'thibault@hyvor.com',
                'lists' => [$list],
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers',
            [
                'email' => 'thibault@hyvor.com',
                'list_ids' => [$list->getId()],
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(
            'Subscriber with email ' . $subscriber->getEmail() . ' already exists',
            $this->getJson()['message']
        );
    }

}
