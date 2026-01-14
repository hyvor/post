<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class ButtonTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $text = "Click me";

        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "button",
                    "attrs" => [
                        "href" => "https://post.hyvor.com",
                    ],
                    "content" => [
                        [
                            "type" => "text",
                            "text" => $text,
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
            "<p class=\"button-wrap\"><a href=\"https://post.hyvor.com\" target=\"_blank\" class=\"button\">$text</a></p>",
            $html,
        );
    }

    public function test_html_to_json(): void
    {
        $text = "Click me";
        $html = "<a class=\"button\" href=\"https://post.hyvor.com\">$text</a>";
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);
        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "button",
                        "attrs" => [
                            "href" => "https://post.hyvor.com",
                        ],
                        "content" => [
                            [
                                "type" => "text",
                                "text" => $text,
                            ],
                        ],
                    ],
                ],
            ]),
            $json,
        );
    }
}
