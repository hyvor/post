<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueService::class)]
#[CoversClass(IssueRepository::class)]
#[CoversClass(Issue::class)]
class CreateIssueTest extends WebTestCase
{

    // TODO: tests for authentication

    public function testCreateSubscriberMinimal(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project]);

        $response = $this->consoleApi(
            $project,
            'POST',
            '/issues',
            [
                'list_id' => $list->getId(),
                'from_email' => 'thibault@hyvor.com',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertIsInt($json['id']);
        $this->assertSame('thibault@hyvor.com', $json['from_email']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($json['id']);
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame('thibault@hyvor.com', $issue->getFromEmail());
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
        $this->assertSame($list->getId(), $issue->getList()->getId());
    }

    public function testCreateSusbscriberWithAllInputs(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project]);

        $scheduledAt = new \DateTimeImmutable('2021-08-27 12:00:00');
        $sendingAt = new \DateTimeImmutable('2021-08-27 12:00:00');
        $failed_at = new \DateTimeImmutable('2021-08-27 12:00:00');
        $sent_at = new \DateTimeImmutable('2021-08-27 12:00:00');

        $response = $this->consoleApi(
            $project,
            'POST',
            '/issues',
            [
                'list_id' => $list->getId(),
                'from_email' => 'thibault@hyvor.com',
                'subject' => 'Test subject',
                'from_name' => 'Thibault',
                'reply_to_email' => 'thibault@hyvor.com',
                'content' => 'Test content',
                'status' => 'draft',
                'html' => 'Test html',
                'text' => 'Test text',
                'error_private' => 'Test error private',
                'batch_id' => 1,
                'scheduled_at' => $scheduledAt->getTimestamp(),
                'sending_at' => $sendingAt->getTimestamp(),
                'failed_at' => $failed_at->getTimestamp(),
                'sent_at' => $sent_at->getTimestamp(),
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertIsInt($json['id']);
        $this->assertSame('thibault@hyvor.com', $json['from_email']);
        $this->assertSame('Test subject', $json['subject']);
        $this->assertSame('Thibault', $json['from_name']);
        $this->assertSame('thibault@hyvor.com', $json['reply_to_email']);
        $this->assertSame('Test content', $json['content']);
        $this->assertSame('draft', $json['status']);
        $this->assertSame('Test html', $json['html']);
        $this->assertSame('Test text', $json['text']);
        $this->assertSame('Test error private', $json['error_private']);
        $this->assertSame(1, $json['batch_id']);
        $this->assertSame($scheduledAt->getTimestamp(), $json['scheduled_at']);
        $this->assertSame($sendingAt->getTimestamp(), $json['sending_at']);
        $this->assertSame($failed_at->getTimestamp(), $json['failed_at']);
        $this->assertSame($sent_at->getTimestamp(), $json['sent_at']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($json['id']);
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame('thibault@hyvor.com', $issue->getFromEmail());
        $this->assertSame('Test subject', $issue->getSubject());
        $this->assertSame('Thibault', $issue->getFromName());
        $this->assertSame('thibault@hyvor.com', $issue->getReplyToEmail());
        $this->assertSame('Test content', $issue->getContent());
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
        $this->assertSame('Test html', $issue->getHtml());
        $this->assertSame('Test text', $issue->getText());
        $this->assertSame('Test error private', $issue->getErrorPrivate());
        $this->assertSame(1, $issue->getBatchId());
        $this->assertSame('2021-08-27 12:00:00', $issue->getScheduledAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-08-27 12:00:00', $issue->getSendingAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-08-27 12:00:00', $issue->getFailedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-08-27 12:00:00', $issue->getSentAt()?->format('Y-m-d H:i:s'));

    }
}
