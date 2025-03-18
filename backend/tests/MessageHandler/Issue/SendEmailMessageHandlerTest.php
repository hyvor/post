<?php

namespace App\Tests\MessageHandler\Issue;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Entity\Type\SubscriberStatus;
use App\Service\Issue\Message\SendEmailMessage;
use App\Service\Issue\MessageHandler\SendEmailMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(SendEmailMessageHandler::class)]
class SendEmailMessageHandlerTest extends KernelTestCase
{

    public function test_send_job(): void
    {

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
        $this->assertSame(new \DateTimeImmutable()->format('Y-m-d H:i:s'), $send->getSentAt()?->format('Y-m-d H:i:s'));

        $this->assertEmailCount(1);

        $email = $this->getMailerMessage();
        $this->assertNotNull($email);
        $this->assertEmailSubjectContains($email, 'Time for Symfony Mailer!');

        $issueRepository = $this->em->getRepository(Issue::class);
        $issueDB = $issueRepository->find($issue->getId());

        // Test checkCompletion method
        $this->assertInstanceOf(Issue::class, $issueDB);
        $this->assertSame($issueDB->getOkSends(), 1);
        $this->assertSame($issueDB->getStatus(), IssueStatus::SENT);
        $this->assertSame($issueDB->getSentAt()?->format('Y-m-d H:i:s'), $send->getSentAt()?->format('Y-m-d H:i:s'));
    }

    public function test_send_job_with_exception(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'project' => $project,
        ]);

        $subscriber = SubscriberFactory::createOne( [
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

        // Not throwing exceptions to test the failure
        $this->transport()->process();

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->find($send->getId());
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame(SendStatus::FAILED, $send->getStatus());

        $issueRepository = $this->em->getRepository(Issue::class);
        $issueDB = $issueRepository->find($issue->getId());

        // Test checkCompletion method
        $this->assertInstanceOf(Issue::class, $issueDB);
        $this->assertSame($issueDB->getFailedSends(), 1);
        $this->assertSame($issueDB->getStatus(), IssueStatus::FAILED);
        $this->assertSame($issueDB->getFailedAt()?->format('Y-m-d H:i:s'), $send->getFailedAt()?->format('Y-m-d H:i:s'));

        $this->assertEmailCount(0);
    }

    public function test_send_job_increase_attempts(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'project' => $project,
        ]);

        $subscriber = SubscriberFactory::createOne( [
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

        // Not throwing exceptions to test the failure
        $this->transport()->process(1);

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->find($send->getId());
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame(SendStatus::PENDING, $send->getStatus());

        $message = $this->transport()->queue()->first()->getMessage();
        $this->assertInstanceOf(SendEmailMessage::class, $message);
        $this->assertSame(2, $message->getAttempt());

        // Test checkCompletion method

        $issueRepository = $this->em->getRepository(Issue::class);
        $issueDB = $issueRepository->find($issue->getId());

        // Test checkCompletion method
        $this->assertInstanceOf(Issue::class, $issueDB);
        $this->assertSame($issueDB->getStatus(), IssueStatus::SENDING);
    }
}
