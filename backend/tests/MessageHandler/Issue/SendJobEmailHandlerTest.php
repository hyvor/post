<?php

namespace App\Tests\MessageHandler\Issue;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SubscriberStatus;
use App\Service\Issue\Message\SendJobMessage;
use App\Service\Issue\MessageHandler\SendJobMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SendJobMessageHandler::class)]
class SendJobEmailHandlerTest extends KernelTestCase
{

    public function test_send_job(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'project' => $project,
        ]);

        $subscribers = SubscriberFactory::createMany(5, [
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
            'status' => IssueStatus::SENDING,
        ]);

        $message = new SendJobMessage($issue->getId(), $send->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport()->throwExceptions()->process();

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->find($send->getId());
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame($send->getStatus(), IssueStatus::SENT);

        $this->assertEmailCount(1);

        $email = $this->getMailerMessage();
        $this->assertNotNull($email);
        $this->assertEmailSubjectContains($email, 'Time for Symfony Mailer!');
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
            'status' => IssueStatus::SENDING,
        ]);

        $message = new SendJobMessage($issue->getId(), $send->getId());
        $this->getMessageBus()->dispatch($message);

        // Not throwing exceptions to test the failure
        $this->transport()->process();

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->find($send->getId());
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame($send->getStatus(), IssueStatus::FAILED);

        $issueRepository = $this->em->getRepository(Issue::class);
        $issueDB = $issueRepository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issueDB);
        $this->assertSame($issueDB->getFailedSends(), 1);
        $this->assertSame($issueDB->getStatus(), IssueStatus::FAILED);

        $this->assertEmailCount(0);
    }
}
