<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class SupTest extends TestCase
{


    public function test_json_to_html(): void
    {
        $text = 'supscript';
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
                                    'type' => 'sup',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->getHtmlFromJson($json);
        $this->assertSame("<p><sup>$text</sup></p>", $html);
    }

    public function test_html_to_json(): void
    {
        $text = 'supscript';
        $html = "<sup>$text</sup>";
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
                                    'type' => 'sup',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }

}