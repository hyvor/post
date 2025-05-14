<?php

namespace App\Tests\Service\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class ParagraphTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $content = 'I am a paragraph';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $content,
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = (new ContentService())->htmlFromJson($json);
        $this->assertSame('<p style="margin: 0 0 20px;line-height:26px;">I am a paragraph</p>', trim($html));
    }

    public function test_html_to_json(): void
    {
        $content = 'A paragraph';
        $html = "<p>$content</p>";
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
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
