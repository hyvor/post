<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueService::class)]
class GetIssueReportTest extends WebTestCase
{

    public function test_get_issue_report_basic(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
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
            $newsletter,
            'GET',
            "/issues/" . $issue->getId() . "/report",
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertArrayHasKey('counts', $json);
        $counts = $json['counts'];
        $this->assertIsArray($counts);
        $this->assertArrayHasKey('total', $counts);
        $this->assertArrayHasKey('sent', $counts);
        $this->assertArrayHasKey('pending', $counts);
        $this->assertSame(1, $counts['total']);
        $this->assertSame(1, $counts['sent']);
        $this->assertSame(0, $counts['pending']);
    }
}
