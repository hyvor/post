<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class LinkTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $content = "Link";
        $href = "https://example.com";
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "paragraph",
                    "content" => [
                        [
                            "type" => "text",
                            "text" => $content,
                            "marks" => [
                                [
                                    "type" => "link",
                                    "attrs" => [
                                        "href" => $href,
                                    ],
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
        $this->assertStringContainsString("<a", $html);
        $this->assertStringContainsString($href, $html);
        $this->assertStringContainsString($content, $html);
    }

    public function test_html_to_json(): void
    {
        $content = "Link";
        $href = "https://example.com";
        $html = "<p><a href=\"$href\">$content</a></p>";
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
                                "text" => $content,
                                "marks" => [
                                    [
                                        "type" => "link",
                                        "attrs" => [
                                            "href" => $href,
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
