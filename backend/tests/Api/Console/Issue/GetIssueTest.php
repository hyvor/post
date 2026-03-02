<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\IssueObject;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueObject::class)]
class GetIssueTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testGetSpecificIssue(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            "/issues/" . $issue->getId()
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertSame($issue->getId(), $json['id']);
        $this->assertSame($issue->getUuid(), $json['uuid']);
        $this->assertSame($issue->getCreatedAt()->getTimestamp(), $json['created_at']);
        $this->assertSame($issue->getSubject(), $json['subject']);
        $this->assertSame($issue->getSendingProfile()->getId(), $json['sending_profile_id']);
        $this->assertSame($issue->getContent(), $json['content']);
        $this->assertSame($issue->getStatus()->value, $json['status']);
        $this->assertSame($issue->getListids(), $json['lists']);
        $this->assertSame($issue->getScheduledAt()?->getTimestamp(), $json['scheduled_at']);
        $this->assertSame($issue->getSendingAt()?->getTimestamp(), $json['sending_at']);
        $this->assertSame($issue->getSentAt()?->getTimestamp(), $json['sent_at']);
    }

    public function testGetSpecificIssueNotFound(): void
    {
        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/issues/999',
            useSession: true
        );

        $this->assertSame(404, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
    }

    public function testGetSpecificIssueNewsletterValidation(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne(['newsletter' => $newsletter1]);

        $response = $this->consoleApi(
            $newsletter2,
            'GET',
            "/issues/" . $issue->getId()
        );

        $this->assertSame(403, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame("Entity does not belong to the newsletter", $json['message']);
    }
}
