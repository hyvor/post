<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class ParagraphTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $content = "I am a paragraph";
        $json = json_encode([
            "type" => "doc",
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
        ]);
        $this->assertIsString($json);
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $html = $contentService->getHtmlFromJson($json);
        $this->assertSame("<p>I am a paragraph</p>", trim($html));
    }

    public function test_html_to_json(): void
    {
        $content = "A paragraph";
        $html = "<p>$content</p>";
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
                            ],
                        ],
                    ],
                ],
            ]),
            $json,
        );
    }
}
