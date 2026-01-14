<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class BlockquoteTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $content = "I am a blockquote";

        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "blockquote",
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

        $this->assertSame(
            "<blockquote>I am a blockquote</blockquote>",
            trim($html),
        );
    }

    public function test_html_to_json(): void
    {
        $content = "A blockquote";

        $html = "<blockquote>$content</blockquote>";

        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);

        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "blockquote",
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
            ]),
            $json,
        );
    }
}
