<?php

namespace App\Content;

use App\Content\Marks\Code;
use App\Content\Marks\Em;
use App\Content\Marks\Link;
use App\Content\Marks\Strike;
use App\Content\Marks\Strong;
use App\Content\Marks\Underline;
use App\Content\Nodes\Blockquote;
use App\Content\Nodes\Button;
use App\Content\Nodes\Doc;
use App\Content\Nodes\HardBreak;
use App\Content\Nodes\Heading;
use App\Content\Nodes\HorizontalRule;
use App\Content\Nodes\Image;
use App\Content\Nodes\Paragraph;
use App\Content\Nodes\Text;
use App\Entity\Issue;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Types\Schema;

class ContentService
{

    public function htmlFromIssue(Issue $issue): string
    {
        $content = $issue->getContent();

        if (!$content) {
            return '';
        }

        return $this->htmlFromJson($content);
    }

    public function textFromIssue(Issue $issue): string
    {
        $content = $issue->getContent();

        if (!$content) {
            return '';
        }

        $document = Document::fromJson($this->getSchema(), $content);
        return $document->toText();
    }

    public function htmlFromJson(string $content): string
    {
        $document = Document::fromJson($this->getSchema(), $content);
        return $document->toHtml();
    }

    public function getSchema(): Schema
    {

        return new Schema(
            [
                new Doc(),
                new Paragraph(),
                new Text(),
                new HardBreak(),
                new Image(),
                new Heading(),
                new HorizontalRule(),
                new Blockquote(),
                new Button()
            ],
            [
                new Em(),
                new Strong(),
                new Link(),
                new Underline(),
                new Strike(),
                new Code(),
            ]
        );

    }

}
