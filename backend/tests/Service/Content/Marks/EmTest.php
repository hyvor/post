<?php

namespace App\Tests\Service\Content\Marks;

use App\Service\Content\ContentService;
use App\Tests\Case\KernelTestCase;

class EmTest extends KernelTestCase
{
    public function test_json_to_html(): void
    {
        $content = 'Emphasized';
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $content,
                            'marks' => [
                                ['type' => 'em'],
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
        $this->assertSame('<p><em>Emphasized</em></p>', $html);
    }

    public function test_html_to_json(): void
    {
        $content = 'Emphasized';
        $html = "<p><em>$content</em></p>";

        /** @var ContentService $contentService */
        $contentService = $this->container->get(ContentService::class);
        $json = ($contentService)->getJsonFromHtml($html);
        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $content,
                            'marks' => [
                                ['type' => 'em'],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
