<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class ListItemTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $content = "I am a list item";

        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "list_item",
                    "content" => [
                        [
                            "type" => "text",
                            "text" => $content,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertIsString($json);
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $html = $contentService->getHtmlFromJson($json);

        $this->assertSame("<li>$content</li>", $html);
    }

    public function test_html_to_json(): void
    {
        $content = "A list item";

        $html = "<li>$content</li>";

        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);

        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "bullet_list",
                        "content" => [
                            [
                                "type" => "list_item",
                                "content" => [
                                    [
                                        "type" => "paragraph",
                                        "content" => [
                                            [
                                                "type" => "text",
                                                "text" => $content,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
            $json,
        );
    }
}
