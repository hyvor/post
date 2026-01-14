<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class FigcaptionTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $caption = "This is a caption";
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "figcaption",
                    "content" => [
                        [
                            "type" => "text",
                            "text" => $caption,
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertIsString($json);
        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $html = $contentService->getHtmlFromJson($json);
        $this->assertSame("<figcaption>$caption</figcaption>", $html);
    }

    // HTML to JSON tested in FigureTest.php
}
