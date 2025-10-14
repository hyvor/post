<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class CodeTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $content = 'Inline code';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'Example Text',
                    'marks' => [
                        [
                            'type' => 'code',
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->getHtmlFromJson($json);
        $this->assertStringContainsString(
            '<code>Example Text</code>',
            $html
        );
    }

    public function test_html_to_json(): void
    {
        $content = 'Inline code';
        $html = "<p><code>$content</code></p>";
        $json = (new ContentService())->getJsonFromHtml($html);
        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $content,
                            'marks' => [
                                ['type' => 'code'],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
