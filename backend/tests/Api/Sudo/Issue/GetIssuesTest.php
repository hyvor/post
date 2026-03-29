<?php

namespace App\Tests\Api\Sudo\Issue;

use App\Api\Sudo\Controller\IssueController;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueService::class)]
class GetIssuesTest extends WebTestCase
{
    public function test_get_issues(): void
    {
        IssueFactory::createMany(3, [
            'status' => IssueStatus::SENT,
        ]);
        IssueFactory::createMany(2, [
            'status' => IssueStatus::DRAFT,
        ]);

        $response = $this->sudoApi(
            'GET',
            '/issues?status=sent'
        );

        $this->assertSame(200, $response->getStatusCode());
        $data = $this->getJson();
        $this->assertCount(3, $data);

        $issue = $data[0];
        $this->assertIsArray($issue);
        $this->assertArrayHasKey('id', $issue);
        $this->assertArrayHasKey('created_at', $issue);
        $this->assertArrayHasKey('uuid', $issue);
        $this->assertArrayHasKey('subject', $issue);
        $this->assertArrayHasKey('status', $issue);
        $this->assertArrayHasKey('scheduled_at', $issue);
        $this->assertArrayHasKey('sending_at', $issue);
        $this->assertArrayHasKey('sent_at', $issue);
        $this->assertArrayHasKey('total_sendable', $issue);
        $this->assertArrayHasKey('error_private', $issue);
    }

    public function test_get_issues_by_subdomain(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        IssueFactory::createMany(3);

        $response = $this->sudoApi(
            'GET',
            "/issues?subdomain={$newsletter->getSubdomain()}"
        );

        $this->assertSame(200, $response->getStatusCode());
        $data = $this->getJson();
        $this->assertCount(1, $data);

        $item = $data[0];
        $this->assertIsArray($item);
        $this->assertSame($issue->getId(), $item['id']);
    }
}
