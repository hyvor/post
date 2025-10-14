<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class EmTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $content = 'Emphasized';
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
                                ['type' => 'em'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->getHtmlFromJson($json);
        $this->assertSame('<p><em>Emphasized</em></p>', $html);
    }

    public function test_html_to_json(): void
    {
        $content = 'Emphasized';
        $html = "<p><em>$content</em></p>";
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
                                ['type' => 'em'],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
