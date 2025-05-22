<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class StrikeTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $content = 'Strikethrough';
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
                                ['type' => 'strike'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->getHtmlFromJson($json);
        $this->assertSame('<p style="margin: 0 0 20px;line-height:26px;"><s>Strikethrough</s></p>', $html);
    }

    public function test_html_to_json(): void
    {
        $content = 'Strikethrough';
        $html = "<p><s>$content</s></p>";
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
                                ['type' => 'strike'],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
