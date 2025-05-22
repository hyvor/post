<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class BlockquoteTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $content = 'I am a blockquote';

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'blockquote',
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
        $html = new ContentService()->getHtmlFromJson($json);

        $this->assertSame(
            '<blockquote style="
            border-left: 4px solid;
            border-color: #000;
            border-color: currentColor;
            border-color: var(--accent);
            margin: 0 0 20px;
            padding: 15px;
        ">I am a blockquote</blockquote>',
            trim($html)
        );
    }

    public function test_html_to_json(): void
    {
        $content = 'A blockquote';

        $html = "<blockquote>$content</blockquote>";

        $json = new ContentService()->getJsonFromHtml($html);

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'blockquote',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $content,
                                ],
                            ]
                        ]
                    ],
                ],
            ],
        ]), $json);
    }
}

