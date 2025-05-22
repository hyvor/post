<?php

namespace App\Tests\Api\Public\Integration\Aws;

use App\Entity\Send;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Service\Integration\Aws\SnsValidationService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AwsWebhookTest extends WebTestCase
{


    /**
     * @param array<string, mixed> $message
     * @param array<string, mixed> $additionalData
     */
    private function callWebhook(
        array $message = [],
        string $type = 'Notification',
        array $additionalData = [],
        bool $mockSns = true,
    ): Response {
        if ($mockSns) {
            $this->mockSnsValidation();
        }

        $data = [
            'Type' => $type,
            'MessageId' => 'dc1e94d9-56c5-5e96-808d-cc7f68faa162',
            'TopicArn' => 'arn:aws:sns:us-east-2:111122223333:ExampleTopic1',
            'Subject' => 'TestSubject',
            'Timestamp' => '2021-02-16T21:41:19.978Z',
            'SignatureVersion' => '1',
            'Signature' => 'MySignature',
            'SigningCertURL' => 'test',
            'UnsubscribeURL' => 'test',
        ];

        $data = array_merge($data, $additionalData);
        $data['Message'] = json_encode($message);

        return $this->publicApi(
            'POST',
            '/integration/aws/webhook',
            $data
        );
    }

    private function mockSnsValidation(): void
    {
        $snsValidationMock = $this->createMock(SnsValidationService::class);
        $snsValidationMock->method('validate')->willReturn(true);
        $this->container->set(SnsValidationService::class, $snsValidationMock);
    }

    public function test_sns_subscription_confirmation(): void
    {
        $subscribeUrl = 'https://example.com/subscribe-url';

        $mockHttpClient = new MockHttpClient(new MockResponse());
        $this->container->set(HttpClientInterface::class, $mockHttpClient);

        $response = $this->callWebhook(type: 'SubscriptionConfirmation', additionalData: [
            'SubscribeURL' => $subscribeUrl,
        ]);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function test_delivery_without_sendid(): void
    {
        $response = $this->callWebhook(
            message: [
                'eventType' => 'Delivery',
                'mail' => [
                    'headers' => [
                        [
                            'name' => 'X-Newsletter-Send-ID',
                        ],
                    ],
                ],
            ],
        );
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function test_delivery(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $send = SendFactory::createOne([
            'issue' => $issue,
        ]);

        $response = $this->callWebhook(
            message: [
                'eventType' => 'Delivery',
                'mail' => [
                    'headers' => [
                        [
                            'name' => 'X-Newsletter-Send-ID',
                            'value' => (string)$send->getId(),
                        ],
                    ],
                ],
                'delivery' => [
                    'timestamp' => '2021-02-16T21:41:19.978Z',
                ]
            ],
        );
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $send = $this->em->getRepository(Send::class)->find($send->getId());
        $this->assertNotNull($send);
        $this->assertSame('2021-02-16 21:41:19', $send->getDeliveredAt()?->format('Y-m-d H:i:s'));
    }

    public function test_complaint(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $send = SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscriber,
        ]);

        $response = $this->callWebhook(
            message: [
                'eventType' => 'Complaint',
                'mail' => [
                    'headers' => [
                        [
                            'name' => 'X-Newsletter-Send-ID',
                            'value' => (string)$send->getId(),
                        ],
                    ],
                ],
                'complaint' => [
                    'timestamp' => '2021-02-16T21:41:19',
                ]
            ],
        );
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $send = $this->em->getRepository(Send::class)->find($send->getId());
        $this->assertNotNull($send);
        $this->assertNotNull($send->getComplainedAt());
        $this->assertSame('2021-02-16 21:41:19', $send->getComplainedAt()->format('Y-m-d H:i:s'));

        $subscriber = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertNotNull($subscriber);
        $this->assertNotNull($subscriber->getUnsubscribedAt());
        $this->assertSame('2021-02-16 21:41:19', $subscriber->getUnsubscribedAt()->format('Y-m-d H:i:s'));
        $this->assertSame(SubscriberStatus::UNSUBSCRIBED, $subscriber->getStatus());
    }

    public function test_soft_bounce(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $send = SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscriber,
        ]);

        $response = $this->callWebhook(
            message: [
                'eventType' => 'Bounce',
                'mail' => [
                    'headers' => [
                        [
                            'name' => 'X-Newsletter-Send-ID',
                            'value' => (string)$send->getId(),
                        ],
                    ],
                ],
                'bounce' => [
                    'timestamp' => '2021-02-16T21:41:19',
                ]
            ],
        );
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $send = $this->em->getRepository(Send::class)->find($send->getId());
        $this->assertNotNull($send);
        $this->assertSame('2021-02-16 21:41:19', $send->getBouncedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame(false, $send->isHardBounce());
    }

    public function test_hard_bounce(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $send = SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscriber,
        ]);

        $response = $this->callWebhook(
            message: [
                'eventType' => 'Bounce',
                'mail' => [
                    'headers' => [
                        [
                            'name' => 'X-Newsletter-Send-ID',
                            'value' => (string)$send->getId(),
                        ],
                    ],
                ],
                'bounce' => [
                    'timestamp' => '2021-02-16T21:41:19',
                    'bounceType' => 'Permanent',
                    'bounceSubType' => 'General',
                ]
            ],
        );
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $send = $this->em->getRepository(Send::class)->find($send->getId());
        $this->assertNotNull($send);
        $this->assertNotNull($send->getBouncedAt());
        $this->assertSame('2021-02-16 21:41:19', $send->getBouncedAt()->format('Y-m-d H:i:s'));
        $this->assertSame(true, $send->isHardBounce());

        $subscriber = $this->em->getRepository(Subscriber::class)->find($subscriber->getId());
        $this->assertNotNull($subscriber);
        $this->assertNotNull($subscriber->getUnsubscribedAt());
        $this->assertSame('Bounce: Permanent - General', $subscriber->getUnsubscribeReason());
    }

    public function test_click_first(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $send = SendFactory::createOne([
            'issue' => $issue,
            'clickCount' => 0,
        ]);

        $response = $this->callWebhook(
            message: [
                'eventType' => 'Click',
                'mail' => [
                    'headers' => [
                        [
                            'name' => 'X-Newsletter-Send-ID',
                            'value' => (string)$send->getId(),
                        ],
                    ],
                ],
                'click' => [
                    'timestamp' => '2021-02-16T21:41:19',
                ]
            ],
        );
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $send = $this->em->getRepository(Send::class)->find($send->getId());
        $this->assertNotNull($send);
        $this->assertNotNull($send->getFirstClickedAt());
        $this->assertSame('2021-02-16 21:41:19', $send->getFirstClickedAt()->format('Y-m-d H:i:s'));
        $this->assertSame(1, $send->getClickCount());
    }

    public function test_click_second(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $send = SendFactory::createOne([
            'issue' => $issue,
            'click_count' => 1,
            'first_clicked_at' => new \DateTimeImmutable('2021-02-16 21:41:19'),
        ]);

        $response = $this->callWebhook(
            message: [
                'eventType' => 'Click',
                'mail' => [
                    'headers' => [
                        [
                            'name' => 'X-Newsletter-Send-ID',
                            'value' => (string)$send->getId(),
                        ],
                    ],
                ],
                'click' => [
                    'timestamp' => '2021-02-16T21:42:19',
                ]
            ],
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $send = $this->em->getRepository(Send::class)->find($send->getId());
        $this->assertNotNull($send);
        $this->assertSame(2, $send->getClickCount());
        $this->assertSame('2021-02-16 21:41:19', $send->getFirstClickedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-02-16 21:42:19', $send->getLastClickedAt()?->format('Y-m-d H:i:s'));
    }

    public function test_open_first(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $send = SendFactory::createOne([
            'issue' => $issue,
        ]);

        $response = $this->callWebhook(
            message: [
                'eventType' => 'Open',
                'mail' => [
                    'headers' => [
                        [
                            'name' => 'X-Newsletter-Send-ID',
                            'value' => (string)$send->getId(),
                        ],
                    ],
                ],
                'open' => [
                    'timestamp' => '2021-02-16T21:41:19',
                ]
            ],
        );
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $send = $this->em->getRepository(Send::class)->find($send->getId());
        $this->assertNotNull($send);
        $this->assertSame('2021-02-16 21:41:19', $send->getFirstOpenedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-02-16 21:41:19', $send->getLastOpenedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame(1, $send->getOpenCount());
    }

    public function test_open_second(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $send = SendFactory::createOne([
            'issue' => $issue,
            'open_count' => 1,
            'first_opened_at' => new \DateTimeImmutable('2021-01-16 21:41:19'),
        ]);

        $response = $this->callWebhook(
            message: [
                'eventType' => 'Open',
                'mail' => [
                    'headers' => [
                        [
                            'name' => 'X-Newsletter-Send-ID',
                            'value' => (string)$send->getId(),
                        ],
                    ],
                ],
                'open' => [
                    'timestamp' => '2021-02-16T21:42:19',
                ]
            ],
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $send = $this->em->getRepository(Send::class)->find($send->getId());
        $this->assertNotNull($send);
        $this->assertSame(2, $send->getOpenCount());
        $this->assertSame('2021-01-16 21:41:19', $send->getFirstOpenedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-02-16 21:42:19', $send->getLastOpenedAt()?->format('Y-m-d H:i:s'));
    }

    public function test_subscription(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $send = SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscriber,
        ]);

        $response = $this->callWebhook(
            message: [
                'eventType' => 'Subscription',
                'mail' => [
                    'headers' => [
                        [
                            'name' => 'X-Newsletter-Send-ID',
                            'value' => (string)$send->getId(),
                        ],
                    ],
                ],
                'subscription' => [
                    'timestamp' => '2021-02-16T21:41:19',
                ]
            ],
        );
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
