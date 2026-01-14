<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class SupTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $text = "supscript";
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "paragraph",
                    "content" => [
                        [
                            "type" => "text",
                            "text" => $text,
                            "marks" => [
                                [
                                    "type" => "sup",
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
        $this->assertSame("<p><sup>$text</sup></p>", $html);
    }

    public function test_html_to_json(): void
    {
        $text = "supscript";
        $html = "<sup>$text</sup>";
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);
        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "paragraph",
                        "content" => [
                            [
                                "type" => "text",
                                "text" => $text,
                                "marks" => [
                                    [
                                        "type" => "sup",
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
