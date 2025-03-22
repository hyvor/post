<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Send;
use App\Entity\Type\SendStatus;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(Send::class)]
class GetIssueProgressTest extends WebTestCase
{
    public function test_issue_progress_pending(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project]);

        $issue = IssueFactory::createOne(
            [
                'project' => $project,
                'total_sends' => 1,
            ]
        );

        $send = SendFactory::createOne(
            [
                'issue' => $issue,
                'status' => SendStatus::PENDING
            ]
        );

        $response = $this->consoleApi(
            $project,
            'GET',
            "/issues/" . $issue->getId() . "/progress",
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame(1, $json['total']);
        $this->assertSame(0, $json['sent']);
        $this->assertSame(1, $json['pending']);
        $this->assertSame(0, $json['progress']);
    }

    public function test_issue_progress_success(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project]);

        $issue = IssueFactory::createOne(
            [
                'project' => $project,
                'total_sends' => 1,
                'ok_sends' => 1,
            ]
        );

        $send = SendFactory::createOne(
            [
                'issue' => $issue,
                'status' => SendStatus::SENT
            ]
        );

        $response = $this->consoleApi(
            $project,
            'GET',
            "/issues/" . $issue->getId() . "/progress",
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame(1, $json['total']);
        $this->assertSame(1, $json['sent']);
        $this->assertSame(0, $json['pending']);
        $this->assertSame(100, $json['progress']);
    }
}
