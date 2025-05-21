<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use PHPUnit\Framework\TestCase;

class HorizontalRuleTest extends TestCase
{
    public function test_json_to_html(): void
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'horizontal_rule',
                ],
            ],
        ]);
        $this->assertIsString($json);
        $html = new ContentService()->htmlFromJson($json);
        $this->assertSame(
            '<hr style="margin:0 0 20px;height:1px;background-color:currentColor;opacity:0.3" />',
            trim($html)
        );
    }

    public function test_html_to_json(): void
    {
        $html = '<hr />';
        $json = (new ContentService())->getJsonFromHtml($html);
        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'horizontal_rule',
                ],
            ],
        ]), $json);
    }
}
