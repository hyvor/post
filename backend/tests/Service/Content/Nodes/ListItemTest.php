<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class ListItemTest extends TestCase
{

    public function test_json_to_html(): void
    {
        $content = 'I am a list item';

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'list_item',
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
        $html = new ContentService()->getHtmlFromJson($json);

        $this->assertSame("<li>$content</li>", $html);
    }

    public function test_html_to_json(): void
    {
        $content = 'A list item';

        $html = "<li>$content</li>";

        $json = new ContentService()->getJsonFromHtml($html);

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bullet_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => $content,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ],
        ]), $json);
    }

}