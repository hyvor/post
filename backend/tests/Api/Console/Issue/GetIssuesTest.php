<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\IssueObject;
use App\Entity\Issue;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Issue::class)]
#[CoversClass(IssueController::class)]
#[CoversClass(IssueService::class)]
#[CoversClass(IssueRepository::class)]
#[CoversClass(IssueObject::class)]
class GetIssuesTest extends WebTestCase
{

    // TODO: tests for authentication

    public function testListIssuesNonEmpty(): void
    {
        $project = ProjectFactory::createOne();
        $issues = IssueFactory::createMany(5, ['project' => $project,]);

        $otherProject = ProjectFactory::createOne();
        IssueFactory::createMany(1, ['project' => $otherProject]);

        $response = $this->consoleApi(
            $project,
            'GET',
            '/issues'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertCount(5, $json);

        $issueInDb = $issues[count($issues) - 1];

        /** @var array<int, array<string, mixed>> $json */
        $issue = $json[0];
        $this->assertSame($issue['id'], $issueInDb->getId());
        // values are tested in GetIssueTest
        $this->assertArrayHasKey('uuid', $issue);
        $this->assertArrayHasKey('created_at', $issue);
        $this->assertArrayHasKey('subject', $issue);
        $this->assertArrayHasKey('from_name', $issue);
        $this->assertArrayHasKey('from_email', $issue);
        $this->assertArrayHasKey('reply_to_email', $issue);
        $this->assertArrayHasKey('content', $issue);
        $this->assertArrayHasKey('status', $issue);
        $this->assertArrayHasKey('lists', $issue);
        $this->assertArrayHasKey('scheduled_at', $issue);
        $this->assertArrayHasKey('sending_at', $issue);
        $this->assertArrayHasKey('sent_at', $issue);
    }


    public function testListIssuesPagination(): void
    {
        $project = ProjectFactory::createOne();
        $issues = IssueFactory::createMany(5, ['project' => $project,]);

        $response = $this->consoleApi(
            $project,
            'GET',
            '/issues?limit=2&offset=1'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertCount(2, $json);

        $issueInDb = $issues[3];

        /** @var array<int, array<string, mixed>> $json */
        $issue = $json[0];
        $this->assertSame($issue['id'], $issueInDb->getId());
        $this->assertArrayHasKey('uuid', $issue);
        $this->assertArrayHasKey('created_at', $issue);
        $this->assertArrayHasKey('subject', $issue);
        $this->assertArrayHasKey('from_name', $issue);
        $this->assertArrayHasKey('from_email', $issue);
        $this->assertArrayHasKey('reply_to_email', $issue);
        $this->assertArrayHasKey('content', $issue);
        $this->assertArrayHasKey('status', $issue);
        $this->assertArrayHasKey('lists', $issue);
        $this->assertArrayHasKey('scheduled_at', $issue);
        $this->assertArrayHasKey('sending_at', $issue);
        $this->assertArrayHasKey('sent_at', $issue);
    }

    public function testListIssuesEmpty(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'GET',
            '/issues'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertCount(0, $json);
    }

    public function testListIssuesProjectValidation(): void
    {
        $project1 = ProjectFactory::createOne();
        $project2 = ProjectFactory::createOne();

        $issuesProject1 = IssueFactory::createMany(5, ['project' => $project1,]);
        $issuesProject2 = IssueFactory::createMany(5, ['project' => $project2,]);

        $response = $this->consoleApi(
            $project1,
            'GET',
            '/issues'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertCount(5, $json);

        /** @var array<int, array<string, mixed>> $json */
        $issue = $json[0];
        $this->assertSame($issuesProject1[count($issuesProject1) - 1]->getId(), $issue['id']);
    }
}
