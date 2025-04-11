<?php

namespace App\Api\Console\Object;

use App\Entity\Project;

class ProjectObject
{

    public int $id;
    public int $created_at; // unix timestamp
    public string $name;

    // Meta fields flattened
    public ?string $templateColorAccent = null;
    public ?string $templateColorBackground = null;
    public ?string $templateColorBoxBackground = null;
    public ?string $templateColorBoxShadow = null;
    public ?string $templateColorBoxBorder = null;
    public ?string $templateFontFamily = null;
    public ?string $templateFontSize = null;
    public ?string $templateFontWeight = null;
    public ?string $templateFontWeightHeading = null;
    public ?string $templateFontColorOnBackground = null;
    public ?string $templateFontColorOnBox = null;
    public ?string $templateFontLineHeight = null;
    public ?string $templateBoxRadius = null;
    public ?string $templateLogo = null;

    public function __construct(Project $project)
    {
        $this->id = $project->getId();
        $this->created_at = $project->getCreatedAt()->getTimestamp();
        $this->name = $project->getName();
        $this->templateColorAccent = $project->getMeta()->templateColorAccent;
        $this->templateColorBackground = $project->getMeta()->templateColorBackground;
        $this->templateColorBoxBackground = $project->getMeta()->templateColorBoxBackground;
        $this->templateColorBoxShadow = $project->getMeta()->templateColorBoxShadow;
        $this->templateColorBoxBorder = $project->getMeta()->templateColorBoxBorder;
        $this->templateFontFamily = $project->getMeta()->templateFontFamily;
        $this->templateFontSize = $project->getMeta()->templateFontSize;
        $this->templateFontWeight = $project->getMeta()->templateFontWeight;
        $this->templateFontWeightHeading = $project->getMeta()->templateFontWeightHeading;
        $this->templateFontColorOnBackground = $project->getMeta()->templateFontColorOnBackground;
        $this->templateFontColorOnBox = $project->getMeta()->templateFontColorOnBox;
        $this->templateFontLineHeight = $project->getMeta()->templateFontLineHeight;
        $this->templateBoxRadius = $project->getMeta()->templateBoxRadius;
        $this->templateLogo = $project->getMeta()->templateLogo;
    }

}
