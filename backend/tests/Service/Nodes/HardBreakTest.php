<?php

namespace App\Tests\Service\Nodes;

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
        $html = (new ContentService())->htmlFromJson($json);
        $this->assertStringContainsString('Line 1', $html);
        $this->assertStringContainsString('<br />', $html);
        $this->assertStringContainsString('Line 2', $html);
    }
}
