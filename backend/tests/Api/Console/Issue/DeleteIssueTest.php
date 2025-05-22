<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
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
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::DRAFT
        ]);

        $issueId = $issue->getId();

        $response = $this->consoleApi(
            $newsletter,
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
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENDING
        ]);

        $issueId = $issue->getId();

        $response = $this->consoleApi(
            $newsletter,
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
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/issues/1'
        );

        $this->assertSame(404, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame("Entity not found", $json['message']);
    }

    public function testCannotDeleteOtherNewsletterIssues(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $otherNewsletter = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter
        ]);

        $response = $this->consoleApi(
            $otherNewsletter,
            'DELETE',
            '/issues/' . $issue->getId()
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Entity does not belong to the newsletter', $this->getJson()['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertNotNull($issue);
    }
}
