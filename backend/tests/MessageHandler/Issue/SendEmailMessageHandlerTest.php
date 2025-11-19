<?php

namespace App\Tests\MessageHandler\Issue;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Entity\Type\SubscriberStatus;
use App\Service\Issue\EmailSenderService;
use App\Service\Issue\Message\SendEmailMessage;
use App\Service\Issue\MessageHandler\SendEmailMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SendingProfileFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[CoversClass(SendEmailMessageHandler::class)]
#[CoversClass(SendEmailMessage::class)]
#[CoversClass(EmailSenderService::class)]
class SendEmailMessageHandlerTest extends KernelTestCase
{
    private function mockHttpClient(Send $send): void
    {
        $callback = function ($method, $url, $options) use ($send): JsonMockResponse {

            $this->assertSame('POST', $method);
            $this->assertSame('https://relay.hyvor.com/api/console/sends', $url);
            $this->assertContains('Content-Type: application/json', $options['headers']);
            $this->assertContains('Authorization: Bearer test-relay-key', $options['headers']);
            $this->assertContains("X-Idempotency-Key: newsletter-send-{$send->getId()}", $options['headers']);

            $body = json_decode($options['body'], true);
            $this->assertIsArray($body);
            $this->assertSame('First Newsletter Issue!', $body['subject']);
            $this->assertSame($send->getEmail(), $body['to']['email']);

            /** @var array<string, string> $emailHeaders */
            $emailHeaders = $body['headers'];
            $this->assertSame($send->getId(), (int)$emailHeaders['X-Newsletter-Send-ID']);
            $this->assertStringStartsWith('<https://post.hyvor.com/api/public/subscriber/unsubscribe?token=', $emailHeaders['List-Unsubscribe']);
            $this->assertSame('List-Unsubscribe=One-Click', $emailHeaders['List-Unsubscribe-Post']);

            return new JsonMockResponse();
        };

        $this->mockRelayClient($callback);
    }

    public function test_send_job(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();
        SendingProfileFactory::findOrCreate([
            'newsletter' => $newsletter,
            'is_system' => true,
        ]);

        $list = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $subscribers = SubscriberFactory::createMany(2, [
            'newsletter' => $newsletter,
            'lists' => [$list],
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'listIds' => [$list->getId()],
            'status' => IssueStatus::SENDING,
            'subject' => 'First Newsletter Issue!',
            'total_sendable' => 1,
        ]);

        $send = SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscribers[0],
        ]);

        $this->mockHttpClient($send);

        $message = new SendEmailMessage($send->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport('async')->throwExceptions()->process();

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->find($send->getId());
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame(SendStatus::SENT, $send->getStatus());
        $this->assertSame('2025-02-21 00:00:00', $send->getSentAt()?->format('Y-m-d H:i:s'));
    }

    public function test_send_job_with_exception(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();
        SendingProfileFactory::findOrCreate([
            'newsletter' => $newsletter,
            'is_system' => true,
        ]);

        $list = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list],
            'email' => 'test_failed@hyvor.com',
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'listIds' => [$list->getId()],
            'status' => IssueStatus::SENDING,
            'total_sendable' => 0,
        ]);

        $send = SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscriber,
            'email' => 'test_failed@hyvor.com',
            'status' => SendStatus::PENDING,
        ]);

        $message = new SendEmailMessage($send->getId());
        $this->getMessageBus()->dispatch($message);

        $emailTransportMock = $this->createMock(EmailSenderService::class);
        $emailTransportMock->expects(self::exactly(4))
            ->method('send')
            ->willThrowException(new \Exception('Email sending failed'));
        $this->container->set(EmailSenderService::class, $emailTransportMock);

        // Not throwing exceptions to test the failure
        $this->transport('async')->process();

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->find($send->getId());
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame(SendStatus::FAILED, $send->getStatus());
        $this->assertSame('2025-02-21 00:00:00', $send->getFailedAt()?->format('Y-m-d H:i:s'));
    }

    #[TestWith([1, 60])]
    #[TestWith([2, 60 * 4])]
    #[TestWith([3, 60 * 16])]
    public function test_send_job_increase_attempts(
        int $attempt,
        int $delaySeconds,
    ): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list],
            'email' => 'test_failed@hyvor.com',
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'listIds' => [$list->getId()],
            'status' => IssueStatus::SENDING,
        ]);

        $send = SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscriber,
            'email' => 'test_failed@hyvor.com',
        ]);

        $message = new SendEmailMessage($send->getId(), $attempt);
        $this->getMessageBus()->dispatch($message);

        $emailTransportMock = $this->createMock(EmailSenderService::class);
        $emailTransportMock->expects(self::once())
            ->method('send')
            ->willThrowException(new \Exception('Email sending failed'));
        $this->container->set(EmailSenderService::class, $emailTransportMock);

        // Not throwing exceptions to test the failure
        $this->transport('async')->process(1);

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->find($send->getId());
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame(SendStatus::PENDING, $send->getStatus());

        $envelope = $this->transport('async')->queue()->first();
        $delay = $envelope->last(DelayStamp::class)?->getDelay();
        $this->assertSame($delaySeconds * 1000, $delay);
        $message = $envelope->getMessage();
        $this->assertInstanceOf(SendEmailMessage::class, $message);
        $this->assertSame($attempt + 1, $message->getAttempt());

        // Test checkCompletion method

        $issueRepository = $this->em->getRepository(Issue::class);
        $issueDB = $issueRepository->find($issue->getId());

        // Test checkCompletion method
        $this->assertInstanceOf(Issue::class, $issueDB);
        $this->assertSame(IssueStatus::SENDING, $issueDB->getStatus());
    }
}
