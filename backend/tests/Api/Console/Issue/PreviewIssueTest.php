<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Issue;
use App\Service\EmailTemplate\HtmlEmailTemplateRenderer;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Issue::class)]
#[CoversClass(IssueController::class)]
#[CoversClass(HtmlEmailTemplateRenderer::class)]
class PreviewIssueTest extends WebTestCase
{
    public function testPreviewIssue(): void
    {
        $project = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne(
            [
                'project' => $project,
                'subject' => 'Test subject',
                'content' => '{"type": "doc"}',
            ]
        );

        $response = $this->consoleApi(
            $project,
            'GET',
            "/issues/" . $issue->getId() . "/preview"
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertArrayHasKey('html', $json);
    }
}
