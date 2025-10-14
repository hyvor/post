<?php

namespace App\Tests\Service\Content\Nodes;

use PHPUnit\Framework\TestCase;

class FigcaptionTest extends TestCase
{

    public function test_json_to_html(): void
    {
        $caption = 'This is a caption';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
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
        ]);
        $this->assertIsString($json);
        $html = new \App\Service\Content\ContentService()->getHtmlFromJson($json);
        $this->assertSame("<figcaption>$caption</figcaption>", $html);
    }

    // HTML to JSON tested in FigureTest.php

}