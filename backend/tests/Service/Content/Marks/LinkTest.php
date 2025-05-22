<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $content = 'Link';
        $href = 'https://example.com';
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
                                [
                                    'type' => 'link',
                                    'attrs' => [
                                        'href' => $href,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = (new ContentService())->getHtmlFromJson($json);
        $this->assertStringContainsString('<a', $html);
        $this->assertStringContainsString($href, $html);
        $this->assertStringContainsString($content, $html);
    }

    public function test_html_to_json(): void
    {
        $content = 'Link';
        $href = 'https://example.com';
        $html = "<p><a href=\"$href\">$content</a></p>";
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
                                [
                                    'type' => 'link',
                                    'attrs' => [
                                        'href' => $href,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
