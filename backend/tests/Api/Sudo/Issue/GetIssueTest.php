<?php

namespace App\Tests\Api\Sudo\Issue;

use App\Api\Sudo\Controller\IssueController;
use App\Api\Sudo\Object\SudoIssueObject;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueService::class)]
#[CoversClass(SudoIssueObject::class)]
class GetIssueTest extends WebTestCase
{
    public function test_get_issue(): void
    {
        $issue = IssueFactory::createOne();

        $response = $this->sudoApi(
            'GET',
            '/issues/' . $issue->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        $data = $this->getJson();
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('subject', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('newsletter_subdomain', $data);
        $this->assertArrayHasKey('newsletter_id', $data);
        $this->assertArrayHasKey('scheduled_at', $data);
        $this->assertArrayHasKey('sending_at', $data);
        $this->assertArrayHasKey('sent_at', $data);
        $this->assertArrayHasKey('total_sendable', $data);
        $this->assertArrayHasKey('error_private', $data);
    }

    public function test_get_issue_not_found(): void
    {
        $response = $this->sudoApi(
            'GET',
            '/issues/99999'
        );

        $this->assertSame(404, $response->getStatusCode());
    }
}
