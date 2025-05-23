<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Issue;
use App\Service\Template\HtmlTemplateRenderer;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Issue::class)]
#[CoversClass(IssueController::class)]
#[CoversClass(HtmlTemplateRenderer::class)]
class PreviewIssueTest extends WebTestCase
{
    public function testPreviewIssue(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
                'subject' => 'Test subject',
                'content' => '{"type": "doc"}',
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            "/issues/" . $issue->getId() . "/preview"
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertArrayHasKey('html', $json);
    }
}
