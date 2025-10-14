<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class StrongTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $content = 'Bold';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $content,
                            'marks' => [
                                ['type' => 'strong'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->getHtmlFromJson($json);
        $this->assertSame('<p><strong>Bold</strong></p>', $html);
    }

    public function test_html_to_json(): void
    {
        $content = 'Bold';
        $html = "<p><strong>$content</strong></p>";
        $json = new ContentService()->getJsonFromHtml($html);
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
                                ['type' => 'strong'],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
