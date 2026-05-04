<?php

namespace App\Tests\Api\Sudo\Issue;

use App\Api\Sudo\Controller\IssueController;
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
    public function test_preview_returns_stored_html(): void
    {
        $issue = IssueFactory::createOne([
            'html' => '<html><body>stored html for preview</body></html>',
        ]);

        $response = $this->sudoApi(
            'GET',
            '/issues/' . $issue->getId() . '/preview'
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringStartsWith(
            'text/html',
            (string)$response->headers->get('Content-Type')
        );
        $this->assertSame(
            '<html><body>stored html for preview</body></html>',
            $response->getContent()
        );
    }

    public function test_preview_not_found(): void
    {
        $response = $this->sudoApi(
            'GET',
            '/issues/99999/preview'
        );

        $this->assertSame(404, $response->getStatusCode());
    }
}
