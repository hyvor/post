<?php

namespace Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\IssueObject;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueObject::class)]
class GetIssueTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testGetSpecificIssue(): void
    {
        $project = ProjectFactory::createOne();
        $issue = IssueFactory::createOne(['project' => $project]);

        $response = $this->consoleApi(
            $project,
            'GET',
            "/issues/" . $issue->getId()
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertSame($issue->getId(), $json['id']);
        $this->assertSame($issue->getUuid(), $json['uuid']);
        $this->assertSame($issue->getCreatedAt()->getTimestamp(), $json['created_at']);
        $this->assertSame($issue->getSubject(), $json['subject']);
        $this->assertSame($issue->getFromName(), $json['from_name']);
        $this->assertSame($issue->getFromEmail(), $json['from_email']);
        $this->assertSame($issue->getReplyToEmail(), $json['reply_to_email']);
        $this->assertSame($issue->getContent(), $json['content']);
        $this->assertSame($issue->getStatus()->value, $json['status']);
        $this->assertSame($issue->getListids(), $json['lists']);
        $this->assertSame($issue->getScheduledAt()?->getTimestamp(), $json['scheduled_at']);
        $this->assertSame($issue->getSendingAt()?->getTimestamp(), $json['sending_at']);
        $this->assertSame($issue->getSentAt()?->getTimestamp(), $json['sent_at']);
    }

    public function testGetSpecificIssueNotFound(): void
    {
        $response = $this->consoleApi(
            null,
            'GET',
            '/issues/999'
        );

        $this->assertSame(404, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
    }

    public function testGetSpecificIssueProjectValidation(): void
    {
        $project1 = ProjectFactory::createOne();
        $project2 = ProjectFactory::createOne();

        $issue = IssueFactory::createOne(['project' => $project1]);

        $response = $this->consoleApi(
            $project2,
            'GET',
            "/issues/" . $issue->getId()
        );

        $this->assertSame(403, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame("Entity does not belong to the project", $json['message']);
    }
}
