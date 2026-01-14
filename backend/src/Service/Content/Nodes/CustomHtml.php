<?php

namespace App\Service\Content\Nodes;

use App\Service\Content\CustomHtmlTwigProcessor;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class CustomHtml extends NodeType
{
    public string $name = 'custom_html';
    public ?string $content = 'text*';
    public string $group = 'block';

    public function __construct(
        private CustomHtmlTwigProcessor $processor
    ) {
    }

    public function toHtml(Node $node, string $children): string
    {
        $code = $node->allText();
        return $this->processor->render($code);
    }
}
