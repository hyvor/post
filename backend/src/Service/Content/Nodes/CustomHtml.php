<?php

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class CustomHtml extends NodeType
{
    public const string MARKER_START = '<!--CUSTOM_HTML_TWIG_START-->';
    public const string MARKER_END = '<!--CUSTOM_HTML_TWIG_END-->';

    public string $name = 'custom_html';
    public ?string $content = 'text*';
    public string $group = 'block';

    public function toHtml(Node $node, string $children): string
    {
        $code = $node->allText();
        return self::MARKER_START . $code . self::MARKER_END;
    }
}