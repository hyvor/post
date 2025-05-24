<?php

namespace App\Service\Content;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ContentDefaultStyle
{

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private ContentService $contentService,
    )
    {
    }

    public function read(): string
    {
        return (string)file_get_contents($this->projectDir . '/templates/newsletter/content-styles.html');
    }

    public function json(): string
    {
        return $this->contentService->getJsonFromHtml($this->read());
    }
    public function html(): string
    {
        return $this->contentService->getHtmlFromJson($this->json());
    }

}
