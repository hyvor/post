<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class HorizontalRuleTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "horizontal_rule",
                ],
            ],
        ]);
        $this->assertIsString($json);
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $html = $contentService->getHtmlFromJson($json);
        $this->assertSame("<hr />", trim($html));
    }

    public function test_html_to_json(): void
    {
        $html = "<hr />";
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);
        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "horizontal_rule",
                    ],
                ],
            ]),
            $json,
        );
    }
}
