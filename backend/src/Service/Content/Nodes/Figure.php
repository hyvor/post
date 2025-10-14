<?php declare(strict_types=1);

namespace App\Service\Content\Nodes;

use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Figure extends NodeType
{

    public string $name = 'figure';
    public ?string $content = 'image figcaption?';
    public string $group = 'block';

    public function fromHtml(): array
    {
        return [
            new ParserRule(tag: 'figure')
        ];
    }

    public function toHtml(Node $node, string $children): string
    {
        return "<figure>$children</figure>";
    }

}