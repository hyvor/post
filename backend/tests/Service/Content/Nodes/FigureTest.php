<?php

namespace App\Tests\Service\Content\Nodes;

use PHPUnit\Framework\TestCase;

class FigureTest extends TestCase
{

    public function test_json_to_html(): void
    {
        $caption = 'This is a caption';
        $src = 'https://example.com/image.jpg';
        $alt = 'Example Image';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => $src,
                                'alt' => $alt,
                            ],
                        ],
                        [
                            'type' => 'figcaption',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $caption,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new \App\Service\Content\ContentService()->getHtmlFromJson($json);
        $this->assertSame("<figure><img src=\"$src\" alt=\"$alt\" /><figcaption>$caption</figcaption></figure>", $html);
    }

    public function test_html_to_json(): void
    {
        $caption = 'This is a caption';
        $src = 'https://example.com/image.jpg';
        $alt = 'Example Image';
        $html = "<figure><img src=\"$src\" alt=\"$alt\" /><figcaption>$caption</figcaption></figure>";
        $json = new \App\Service\Content\ContentService()->getJsonFromHtml($html);
        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'figure',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => $src,
                                'alt' => $alt,
                                'width' => null,
                                'height' => null,
                            ],
                        ],
                        [
                            'type' => 'figcaption',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $caption,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }

}