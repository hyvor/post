<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class ImageTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $src = "https://example.com/image.png";
        $alt = "Alt text";
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "image",
                    "attrs" => [
                        "src" => $src,
                        "alt" => $alt,
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $html = $contentService->getHtmlFromJson($json);
        $this->assertSame(
            '<img src="https://example.com/image.png" alt="Alt text" />',
            trim($html),
        );
    }

    public function test_json_to_html_with_width_height(): void
    {
        $src = "https://example.com/image.png";
        $alt = "Alt text";
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "image",
                    "attrs" => [
                        "src" => $src,
                        "alt" => $alt,
                        "width" => "600",
                        "height" => "400",
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $html = $contentService->getHtmlFromJson($json);
        $this->assertSame(
            '<img src="https://example.com/image.png" alt="Alt text" width="600" height="400" />',
            trim($html),
        );
    }

    public function test_html_to_json(): void
    {
        $src = "https://example.com/image.png";
        $alt = "Alt text";
        $html = "<img src=\"$src\" alt=\"$alt\" />";
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);
        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "image",
                        "attrs" => [
                            "src" => $src,
                            "alt" => $alt,
                            "width" => null,
                            "height" => null,
                        ],
                    ],
                ],
            ]),
            $json,
        );
    }
}
