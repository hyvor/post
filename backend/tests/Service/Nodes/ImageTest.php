<?php

namespace App\Tests\Service\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $src = 'https://example.com/image.png';
        $alt = 'Alt text';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => $src,
                        'alt' => $alt,
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = (new ContentService())->htmlFromJson($json);
        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString($src, $html);
        $this->assertStringContainsString($alt, $html);
    }

    public function test_html_to_json(): void
    {
        $src = 'https://example.com/image.png';
        $alt = 'Alt text';
        $html = "<img src=\"$src\" alt=\"$alt\" />";
        $json = (new ContentService())->getJsonFromHtml($html);
        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => $src,
                        'alt' => $alt,
                    ],
                ],
            ],
        ]), $json);
    }
}
