<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class HardBreakTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Line 1',
                        ],
                        [
                            'type' => 'hard_break',
                        ],
                        [
                            'type' => 'text',
                            'text' => 'Line 2',
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->getHtmlFromJson($json);
        $this->assertSame('<p style="margin: 0 0 20px;line-height:26px;">Line 1<br />Line 2</p>', $html);
    }
}
