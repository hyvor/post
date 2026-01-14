<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class OrderedListTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $content1 = "First item";
        $content2 = "Second item";

        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "ordered_list",
                    "content" => [
                        [
                            "type" => "list_item",
                            "content" => [
                                [
                                    "type" => "text",
                                    "text" => $content1,
                                ],
                            ],
                        ],
                        [
                            "type" => "list_item",
                            "content" => [
                                [
                                    "type" => "text",
                                    "text" => $content2,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertIsString($json);
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $html = $contentService->getHtmlFromJson($json);

        $this->assertSame(
            "<ol><li>$content1</li><li>$content2</li></ol>",
            $html,
        );
    }

    public function test_html_to_json(): void
    {
        $content1 = "First item";
        $content2 = "Second item";

        $html = "<ol><li>$content1</li><li>$content2</li></ol>";

        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);

        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "ordered_list",
                        "content" => [
                            [
                                "type" => "list_item",
                                "content" => [
                                    [
                                        "type" => "paragraph",
                                        "content" => [
                                            [
                                                "type" => "text",
                                                "text" => $content1,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                "type" => "list_item",
                                "content" => [
                                    [
                                        "type" => "paragraph",
                                        "content" => [
                                            [
                                                "type" => "text",
                                                "text" => $content2,
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
