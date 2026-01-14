<?php

namespace App\Tests\Service\Content\Nodes;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class CodeBlockTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $code = 'echo "Hello, World!";';
        $json = json_encode([
            "type" => "doc",
            "content" => [
                [
                    "type" => "code_block",
                    "content" => [
                        [
                            "type" => "text",
                            "text" => $code,
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
            "<pre><code>echo &quot;Hello, World!&quot;;</code></pre>",
            $html,
        );
    }

    public function test_html_to_json(): void
    {
        $code = 'echo "Hello, World!";';
        $html = "<pre><code>$code</code></pre>";

        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = $contentService->getJsonFromHtml($html);
        $this->assertSame(
            json_encode([
                "type" => "doc",
                "content" => [
                    [
                        "type" => "code_block",
                        "content" => [
                            [
                                "type" => "text",
                                "text" => $code,
                            ],
                        ],
                    ],
                ],
            ]),
            $json,
        );
    }
}
