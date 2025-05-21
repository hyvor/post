<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $text = 'Click me';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'button',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $text,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->htmlFromJson($json);
        $this->assertSame('<button><p style="margin: 0 0 20px;line-height:26px;">Click me</p></button>', $html);
    }

    public function test_html_to_json(): void
    {
        $text = 'Click me';
        $html = "<button><p>$text</p></button>";
        $json = (new ContentService())->getJsonFromHtml($html);
        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'button',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $text,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
