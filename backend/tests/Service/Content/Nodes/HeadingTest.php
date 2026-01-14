<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class HeadingTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $content = "Heading 2";
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "heading",
                    "attrs" => ["level" => 2, "id" => null],
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
        $this->assertSame("<h2>Heading 2</h2>", trim($html));
    }

    public function test_json_to_html_with_id(): void
    {
        $content = "Heading 2";
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "heading",
                    "attrs" => ["level" => 2, "id" => "custom-id"],
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
        $this->assertSame('<h2 id="custom-id">Heading 2</h2>', trim($html));
    }

    public function test_html_to_json(): void
    {
        $content = "Heading 3";
        $html = "<h3>$content</h3>";
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);
        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "heading",
                        "attrs" => [
                            "level" => 3,
                            "id" => null,
                        ],
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

    public function test_html_to_json_with_id(): void
    {
        $content = "Heading 3";
        $html = '<h3 id="custom-id">' . $content . "</h3>";
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);
        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "heading",
                        "attrs" => [
                            "level" => 3,
                            "id" => "custom-id",
                        ],
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
