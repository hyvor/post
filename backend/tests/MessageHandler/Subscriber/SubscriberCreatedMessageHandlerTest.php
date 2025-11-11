<?php

namespace App\Tests\MessageHandler\Subscriber;

use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Service\Subscriber\Message\SubscriberCreatedMessage;
use App\Service\Subscriber\MessageHandler\SubscriberCreatedMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SendingProfileFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(SubscriberCreatedMessageHandler::class)]
#[CoversClass(SubscriberCreatedMessage::class)]
class SubscriberCreatedMessageHandlerTest extends KernelTestCase
{
    public function test_send_confirmation_email_for_pending_subscriber(): void
    {
        Clock::set(new MockClock('2025-11-11 10:00:00'));

        $newsletter = NewsletterFactory::createOne([
            'name' => 'My Test Newsletter',
        ]);

        $list = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list],
            'email' => 'subscriber@example.com',
            'status' => SubscriberStatus::PENDING,
        ]);

        $sendingProfile = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'from_email' => 'thibault@hvrpst.com',
            'is_default' => true,
            'is_system' => true,
        ]);

        $callback = function ($method, $url, $options) use ($subscriber, $newsletter): JsonMockResponse {
            $this->assertSame('POST', $method);
            $this->assertStringStartsWith('https://relay.hyvor.com/api/console/', $url);
            $this->assertContains('Content-Type: application/json', $options['headers']);
            $this->assertContains('Authorization: Bearer test-relay-key', $options['headers']);

            $body = json_decode($options['body'], true);
            $this->assertIsArray($body);
            $this->assertSame('Confirm your subscription to ' . $newsletter->getName(), $body['subject']);
            $this->assertSame($subscriber->getEmail(), $body['to']['email']);

            return new JsonMockResponse();
        };

        $this->mockRelayClient($callback);

        $message = new SubscriberCreatedMessage($subscriber->getId());
        $this->getMessageBus()->dispatch($message);

        $transport = $this->transport('async');
        $transport->throwExceptions()->process();

        $subscriberRepository = $this->em->getRepository(Subscriber::class);
        $subscriberDB = $subscriberRepository->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $subscriberDB);
        $this->assertSame(SubscriberStatus::PENDING, $subscriberDB->getStatus());
    }
}
