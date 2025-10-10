<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class SubTest extends TestCase
{

    public function test_json_to_html(): void
    {
        $text = 'subscript';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $text,
                            'marks' => [
                                [
                                    'type' => 'sub',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->getHtmlFromJson($json);
        $this->assertSame("<p><sub>$text</sub></p>", $html);
    }

    public function test_html_to_json(): void
    {
        $text = 'subscript';
        $html = "<sub>$text</sub>";
        $json = (new ContentService())->getJsonFromHtml($html);
        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $text,
                            'marks' => [
                                [
                                    'type' => 'sub',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }

}