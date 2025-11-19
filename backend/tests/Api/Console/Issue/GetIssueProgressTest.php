<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Send;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(Send::class)]
class GetIssueProgressTest extends WebTestCase
{
    public function test_issue_progress_pending(): void
    {
        $newsletter = NewsletterFactory::createOne();
        NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'total_sendable' => 20
        ]);

        SendFactory::createMany(15, [
            'issue' => $issue,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            "/issues/" . $issue->getId() . "/progress",
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame(20, $json['total']);
        $this->assertSame(15, $json['sent']);
        $this->assertSame(75, $json['progress']);
    }

    public function test_issue_progress_success(): void
    {
        $newsletter = NewsletterFactory::createOne();
        NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'total_sendable' => 1
        ]);

        SendFactory::createOne([
            'issue' => $issue,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            "/issues/" . $issue->getId() . "/progress",
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertSame(1, $json['total']);
        $this->assertSame(1, $json['sent']);
        $this->assertSame(100, $json['progress']);
    }
}
