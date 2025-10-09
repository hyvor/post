<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class UnorderedListTest extends TestCase
{

    public function test_json_to_html(): void
    {
        $content1 = 'First item';
        $content2 = 'Second item';

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bullet_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $content1,
                                ],
                            ],
                        ],
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $content2,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertIsString($json);
        $html = new ContentService()->getHtmlFromJson($json);

        $this->assertSame("<ul><li>$content1</li><li>$content2</li></ul>", $html);
    }

    public function test_html_to_json(): void
    {
        $content1 = 'First item';
        $content2 = 'Second item';

        $html = "<ul><li>$content1</li><li>$content2</li></ul>";

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
                                            'text' => $content1,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => $content2,
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