<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class CalloutTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $emoji = "💡";
        $bg = "#FFFBCC";
        $fg = "#333333";
        $text = "This is a callout";
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "callout",
                    "attrs" => [
                        "emoji" => $emoji,
                        "bg" => $bg,
                        "fg" => $fg,
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
            "<div class=\"callout\" style=\"background-color:$bg;color:$fg\"><span>$emoji</span><div>$text</div></div>",
            $html,
        );
    }

    public function test_html_to_json(): void
    {
        $emoji = "💡";
        $bg = "#FFFBCC";
        $fg = "#333333";
        $text = "This is a callout";
        $html = "<div class=\"callout\" style=\"background-color:$bg;color:$fg\"><span>$emoji</span><div><p>$text</p></div></div>";
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);
        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "callout",
                        "attrs" => [
                            "emoji" => $emoji,
                            "bg" => $bg,
                            "fg" => $fg,
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
