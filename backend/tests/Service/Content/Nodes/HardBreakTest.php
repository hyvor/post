<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class HardBreakTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "paragraph",
                    "content" => [
                        [
                            "type" => "text",
                            "text" => "Line 1",
                        ],
                        [
                            "type" => "hard_break",
                        ],
                        [
                            "type" => "text",
                            "text" => "Line 2",
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);

        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $html = $contentService->getHtmlFromJson($json);
        $this->assertSame("<p>Line 1<br />Line 2</p>", $html);
    }
}
