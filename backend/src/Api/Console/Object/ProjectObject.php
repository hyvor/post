<?php

namespace App\Api\Console\Object;

use App\Entity\Project;
use App\Entity\User;
use Hyvor\Internal\Auth\AuthUser;

class ProjectObject
{

    public int $id;
    public int $created_at; // unix timestamp
    public string $name;
    public UserObject $current_user; // Current user in the project

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

    public function __construct(Project $project, User $projectUser, AuthUser $authUser)
    {
        $this->id = $project->getId();
        $this->created_at = $project->getCreatedAt()->getTimestamp();
        $this->name = $project->getName();
        $this->current_user = new UserObject($projectUser, $authUser);

        $meta = $project->getMeta();
        $this->template_color_accent = $meta->template_color_accent;
        $this->template_color_background = $meta->template_color_background;
        $this->template_color_box_background = $meta->template_color_box_background;
        $this->template_color_box_shadow = $meta->template_color_box_shadow;
        $this->template_color_box_border = $meta->template_color_box_border;
        $this->template_font_family = $meta->template_font_family;
        $this->template_font_size = $meta->template_font_size;
        $this->template_font_weight = $meta->template_font_weight;
        $this->template_font_weight_heading = $meta->template_font_weight_heading;
        $this->template_font_color_on_background = $meta->template_font_color_on_background;
        $this->template_font_color_on_box = $meta->template_font_color_on_box;
        $this->template_font_line_height = $meta->template_font_line_height;
        $this->template_box_radius = $meta->template_box_radius;
        $this->template_logo = $meta->template_logo;
    }

}
