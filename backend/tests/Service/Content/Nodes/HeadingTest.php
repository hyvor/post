<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class HeadingTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $content = 'Heading 2';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2],
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
        $html = new ContentService()->htmlFromJson($json);
        $this->assertSame(
            '<h2 style="margin: 0 0 20px; font-size: 28px; font-weight: bold;">Heading 2</h2>',
            trim($html)
        );
    }

    public function test_html_to_json(): void
    {
        $content = 'Heading 3';
        $html = "<h3>$content</h3>";
        $json = (new ContentService())->getJsonFromHtml($html);
        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 3],
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
