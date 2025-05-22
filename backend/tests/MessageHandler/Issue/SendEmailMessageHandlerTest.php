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
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[CoversClass(SendEmailMessageHandler::class)]
#[CoversClass(SendEmailMessage::class)]
#[CoversClass(EmailSenderService::class)]
class SendEmailMessageHandlerTest extends KernelTestCase
{

    public function test_send_job(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'project' => $project,
        ]);

        $subscribers = SubscriberFactory::createMany(2, [
            'project' => $project,
            'lists' => [$list],
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'listIds' => [$list->getId()],
            'status' => IssueStatus::SENDING,
            'subject' => 'First Newsletter Issue!',
        ]);

        $send = SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscribers[0],
        ]);

        $message = new SendEmailMessage($send->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport()->throwExceptions()->process();

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->find($send->getId());
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame(SendStatus::SENT, $send->getStatus());
        $this->assertSame('2025-02-21 00:00:00', $send->getSentAt()?->format('Y-m-d H:i:s'));

        $this->assertEmailCount(1);

        $email = $this->getMailerMessage();
        $this->assertNotNull($email);
        $this->assertEmailSubjectContains($email, 'First Newsletter Issue!');

        $issueRepository = $this->em->getRepository(Issue::class);
        $issueDB = $issueRepository->find($issue->getId());

        // Test checkCompletion method
        $this->assertInstanceOf(Issue::class, $issueDB);
        $this->assertSame($issueDB->getOkSends(), 1);
        $this->assertSame($issueDB->getStatus(), IssueStatus::SENT);
        $this->assertSame("2025-02-21 00:00:00", $issueDB->getSentAt()?->format('Y-m-d H:i:s'));
    }

    public function test_send_job_with_exception(): void
    {
        Clock::set(new MockClock('2025-02-21'));


        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'project' => $project,
        ]);

        $subscriber = SubscriberFactory::createOne([
            'project' => $project,
            'lists' => [$list],
            'email' => 'test_failed@hyvor.com',
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'listIds' => [$list->getId()],
            'status' => IssueStatus::SENDING,
        ]);

        $send = SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscriber,
            'email' => 'test_failed@hyvor.com',
        ]);

        $message = new SendEmailMessage($send->getId());
        $this->getMessageBus()->dispatch($message);

        $emailTransportMock = $this->createMock(EmailSenderService::class);
        $emailTransportMock->expects(self::exactly(4))
            ->method('send')
            ->willThrowException(new \Exception('Email sending failed'));
        $this->container->set(EmailSenderService::class, $emailTransportMock);

        // Not throwing exceptions to test the failure
        $this->transport()->process();

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->find($send->getId());
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame(SendStatus::FAILED, $send->getStatus());
        $this->assertSame('2025-02-21 00:00:00', $send->getFailedAt()?->format('Y-m-d H:i:s'));

        $issueRepository = $this->em->getRepository(Issue::class);
        $issueDB = $issueRepository->find($issue->getId());

        // Test checkCompletion method
        $this->assertInstanceOf(Issue::class, $issueDB);
        $this->assertSame($issueDB->getFailedSends(), 1);
        $this->assertSame($issueDB->getStatus(), IssueStatus::FAILED);
        $this->assertSame("2025-02-21 00:00:00", $issueDB->getFailedAt()?->format('Y-m-d H:i:s'));

        $this->assertEmailCount(0);
    }

    #[TestWith([1, 60])]
    #[TestWith([2, 60 * 4])]
    #[TestWith([3, 60 * 16])]
    public function test_send_job_increase_attempts(
        int $attempt,
        int $delaySeconds,
    ): void {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'project' => $project,
        ]);

        $subscriber = SubscriberFactory::createOne([
            'project' => $project,
            'lists' => [$list],
            'email' => 'test_failed@hyvor.com',
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
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
        $this->transport()->process(1);

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->find($send->getId());
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame(SendStatus::PENDING, $send->getStatus());

        $envelope = $this->transport()->queue()->first();
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
