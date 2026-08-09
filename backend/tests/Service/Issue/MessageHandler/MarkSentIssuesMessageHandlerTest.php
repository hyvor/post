<?php

namespace Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Service\Issue\Message\MarkSentIssuesMessage;
use App\Service\Issue\MessageHandler\MarkSentIssuesMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MarkSentIssuesMessageHandler::class)]
#[CoversClass(MarkSentIssuesMessage::class)]
#[CoversClass(Issue::class)]
class MarkSentIssuesMessageHandlerTest extends KernelTestCase
{
    private function runHandler(): void
    {
        $transport = $this->transport('scheduler_default');
        $transport->send(new MarkSentIssuesMessage());
        $transport->throwExceptions()->process();
    }

    public function test_marks_issue_sent_when_queued_and_no_pending(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENDING,
            'queued_at' => new \DateTimeImmutable('-1 hour'),
            'sent_at' => null,
        ]);

        SendFactory::createMany(2, [
            'issue' => $issue,
            'newsletter' => $newsletter,
            'status' => SendStatus::SENT,
        ]);

        $this->runHandler();

        $this->assertSame(IssueStatus::SENT, $issue->getStatus());
        $this->assertNotNull($issue->getSentAt());
    }

    public function test_does_not_mark_sent_when_pending_sends_exist(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENDING,
            'queued_at' => new \DateTimeImmutable('-1 hour'),
        ]);

        SendFactory::createOne([
            'issue' => $issue,
            'newsletter' => $newsletter,
            'status' => SendStatus::SENT,
        ]);
        SendFactory::createOne([
            'issue' => $issue,
            'newsletter' => $newsletter,
            'status' => SendStatus::PENDING,
        ]);

        $this->runHandler();

        $this->assertSame(IssueStatus::SENDING, $issue->getStatus());
    }

    public function test_does_not_mark_sent_when_not_queued(): void
    {
        $newsletter = NewsletterFactory::createOne();

        // queued_at is null: fan-out has not finished, so it must not be flipped
        // even though there are no pending sends.
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENDING,
            'queued_at' => null,
        ]);

        SendFactory::createOne([
            'issue' => $issue,
            'newsletter' => $newsletter,
            'status' => SendStatus::SENT,
        ]);

        $this->runHandler();

        $this->assertSame(IssueStatus::SENDING, $issue->getStatus());
    }
}
