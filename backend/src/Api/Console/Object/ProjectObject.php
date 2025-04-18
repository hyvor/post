<?php

namespace App\Api\Console\Object;

use App\Entity\Project;

class ProjectObject
{

    public int $id;
    public int $created_at; // unix timestamp
    public string $name;

    // Meta fields flattened
    public ?string $template_color_accent = null;
    public ?string $template_color_background = null;
    public ?string $template_color_box_background = null;
    public ?string $template_color_box_shadow = null;
    public ?string $template_color_box_border = null;
    public ?string $template_font_family = null;
    public ?string $template_font_size = null;
    public ?string $template_font_weight = null;
    public ?string $template_font_weight_heading = null;
    public ?string $template_font_color_on_background = null;
    public ?string $template_font_color_on_box = null;
    public ?string $template_font_line_height = null;
    public ?string $template_box_radius = null;
    public ?string $template_logo = null;

    public function __construct(Project $project)
    {
        $this->id = $project->getId();
        $this->created_at = $project->getCreatedAt()->getTimestamp();
        $this->name = $project->getName();
        $this->template_color_accent = $project->getMeta()->template_color_accent;
        $this->template_color_background = $project->getMeta()->template_color_background;
        $this->template_color_box_background = $project->getMeta()->template_color_box_background;
        $this->template_color_box_shadow = $project->getMeta()->template_color_box_shadow;
        $this->template_color_box_border = $project->getMeta()->template_color_box_border;
        $this->template_font_family = $project->getMeta()->template_font_family;
        $this->template_font_size = $project->getMeta()->template_font_size;
        $this->template_font_weight = $project->getMeta()->template_font_weight;
        $this->template_font_weight_heading = $project->getMeta()->template_font_weight_heading;
        $this->template_font_color_on_background = $project->getMeta()->template_font_color_on_background;
        $this->template_font_color_on_box = $project->getMeta()->template_font_color_on_box;
        $this->template_font_line_height = $project->getMeta()->template_font_line_height;
        $this->template_box_radius = $project->getMeta()->template_box_radius;
        $this->template_logo = $project->getMeta()->template_logo;
    }

}
