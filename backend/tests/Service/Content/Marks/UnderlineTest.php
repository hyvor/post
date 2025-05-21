<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class UnderlineTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $content = 'Underlined';
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
                                ['type' => 'underline'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->htmlFromJson($json);
        $this->assertStringContainsString(
            '<p style="margin: 0 0 20px;line-height:26px;"><span style="text-decoration:underline">Underlined</span></p>',
            $html
        );
    }

    public function test_html_to_json(): void
    {
        $content = 'Underlined';
        $html = "<p><span style=\"text-decoration: underline\">$content</span></p>";
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
                                ['type' => 'underline'],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
