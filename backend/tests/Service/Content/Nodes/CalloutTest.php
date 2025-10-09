<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class CalloutTest extends TestCase
{

    public function test_json_to_html(): void
    {
        $emoji = '💡';
        $bg = '#FFFBCC';
        $fg = '#333333';
        $text = 'This is a callout';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'callout',
                    'attrs' => [
                        'emoji' => $emoji,
                        'bg' => $bg,
                        'fg' => $fg,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $text,
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->getHtmlFromJson($json);
        $this->assertSame("<div class=\"callout\" style=\"background-color:$bg;color:$fg\"><span>$emoji</span><div>$text</div></div>", $html);
    }

    public function test_html_to_json(): void
    {
        $emoji = '💡';
        $bg = '#FFFBCC';
        $fg = '#333333';
        $text = 'This is a callout';
        $html = "<div class=\"callout\" style=\"background-color:$bg;color:$fg\"><span>$emoji</span><div><p>$text</p></div></div>";
        $json = new ContentService()->getJsonFromHtml($html);
        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'callout',
                    'attrs' => [
                        'emoji' => $emoji,
                        'bg' => $bg,
                        'fg' => $fg,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $text,
                        ],
                    ],
                ],
            ],
        ]), $json);
    }

}