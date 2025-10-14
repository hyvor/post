<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class CustomHtmlTest extends TestCase
{

    public function test_json_to_html(): void
    {
        $code = '<div style="color: red;">Hello World</div>';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'custom_html',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $code,
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->getHtmlFromJson($json);
        $this->assertSame("<div>$code</div>", $html);
    }

}