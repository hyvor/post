<?php

namespace App\Tests\Api\Public\Template;

use App\Api\Public\Controller\Template\TemplateController;
use App\Content\ContentService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TemplateController::class)]
#[CoversClass(ContentService::class)]
class TemplateGetHtmlContentTest extends WebTestCase
{
    public function test_get_html_content(): void
    {
        $response = $this->publicApi(
            'POST',
            '/template/content',
            [
                'content' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hello World"}]}]}'
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertArrayHasKey('html', $json);
        $this->assertIsString($json['html']);
        $this->assertStringContainsString('Hello World', $json['html']);
    }
}
