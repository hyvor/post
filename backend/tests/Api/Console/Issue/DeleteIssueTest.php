<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueService::class)]
#[CoversClass(IssueRepository::class)]
#[CoversClass(Issue::class)]
class DeleteIssueTest extends WebTestCase
{

    // TODO: tests for authentication

    public function testDeleteDraftIssue(): void
    {
        $project = ProjectFactory::createOne();
        $issue = IssueFactory::createOne([
            'project' => $project,
            'status' => IssueStatus::DRAFT
        ]);

        $issueId = $issue->getId();

        $response = $this->consoleApi(
          $project,
          'DELETE',
          '/issues/' . $issue->getId(),
        );

        $this->assertSame(200, $response->getStatusCode());

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issueId);
        $this->assertNull($issue);
    }

    public function testDeleteNonDraftIssue(): void
    {
        $project = ProjectFactory::createOne();
        $issue = IssueFactory::createOne([
            'project' => $project,
            'status' => IssueStatus::SENDING
        ]);

        $issueId = $issue->getId();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/issues/' . $issue->getId(),
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame("Issue is not a draft.", $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issueId);
        $this->assertNotNull($issue);

    }

    public function testDeleteIssueNotFound(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/issues/1'
        );

        $this->assertSame(404, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame("Entity not found", $json['message']);

    }

    public function testCannotDeleteOtherProjectIssues(): void
    {
        $project = ProjectFactory::createOne();
        $otherProject = ProjectFactory::createOne();

        $issue = IssueFactory::createOne([
            'project' => $project
        ]);

        $response = $this->consoleApi(
            $otherProject,
            'DELETE',
            '/issues/' . $issue->getId()
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Entity does not belong to the project', $this->getJson()['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertNotNull($issue);
    }
}
