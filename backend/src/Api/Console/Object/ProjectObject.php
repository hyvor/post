<?php

namespace App\Api\Console\Object;

use App\Entity\Project;
use App\Entity\User;
use Hyvor\Internal\Auth\AuthUser;

class ProjectObject
{

    public int $id;
    public string $uuid;
    public int $created_at; // unix timestamp
    public string $name;
    public string $default_email_username;

    public ?string $template_color_accent;
    public ?string $template_color_background;
    public ?string $template_color_box_background;
    public ?string $template_color_box_shadow;
    public ?string $template_color_box_border;
    public ?string $template_font_family;
    public ?string $template_font_size;
    public ?string $template_font_weight;
    public ?string $template_font_weight_heading;
    public ?string $template_font_color_on_background;
    public ?string $template_font_color_on_box;
    public ?string $template_font_line_height;
    public ?string $template_box_radius;
    public ?string $template_logo;

    public ?string $form_title;
    public ?string $form_description;
    public ?string $form_footer_text;
    public ?string $form_button_text;
    public ?string $form_success_message;

    public ?int $form_width; // null = 100%
    public ?string $form_custom_css;

    public ?string $form_color_light_text; // null = inherit
    public ?string $form_color_light_text_light;
    public ?string $form_color_light_accent;
    public ?string $form_color_light_accent_text;
    public ?string $form_color_light_input;
    public ?string $form_color_light_input_text;
    public ?string $form_light_input_box_shadow;
    public ?string $form_light_input_border;
    public ?int $form_light_border_radius;

    public ?string $form_color_dark_text; // null = inherit
    public ?string $form_color_dark_text_light;
    public ?string $form_color_dark_accent;
    public ?string $form_color_dark_accent_text;
    public ?string $form_color_dark_input;
    public ?string $form_color_dark_input_text;
    public ?string $form_dark_input_box_shadow;
    public ?string $form_dark_input_border;
    public ?int $form_dark_border_radius;

    public function __construct(Project $project)
    {
        $this->id = $project->getId();
        $this->uuid = $project->getUuid();
        $this->created_at = $project->getCreatedAt()->getTimestamp();
        $this->name = $project->getName();
        $this->default_email_username = $project->getDefaultEmailUsername();

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

        $this->form_width = $meta->form_width;
        $this->form_custom_css = $meta->form_custom_css;
        $this->form_title = $meta->form_title;
        $this->form_description = $meta->form_description;
        $this->form_footer_text = $meta->form_footer_text;
        $this->form_button_text = $meta->form_button_text;
        $this->form_success_message = $meta->form_success_message;

        $this->form_color_light_text = $meta->form_color_light_text;
        $this->form_color_light_text_light = $meta->form_color_light_text_light;
        $this->form_color_light_accent = $meta->form_color_light_accent;
        $this->form_color_light_accent_text = $meta->form_color_light_accent_text;
        $this->form_color_light_input = $meta->form_color_light_input;
        $this->form_color_light_input_text = $meta->form_color_light_input_text;
        $this->form_light_input_box_shadow = $meta->form_light_input_box_shadow;
        $this->form_light_input_border = $meta->form_light_input_border;
        $this->form_light_border_radius = $meta->form_light_border_radius;

        $this->form_color_dark_text = $meta->form_color_dark_text;
        $this->form_color_dark_text_light = $meta->form_color_dark_text_light;
        $this->form_color_dark_accent = $meta->form_color_dark_accent;
        $this->form_color_dark_accent_text = $meta->form_color_dark_accent_text;
        $this->form_color_dark_input = $meta->form_color_dark_input;
        $this->form_color_dark_input_text = $meta->form_color_dark_input_text;
        $this->form_dark_input_box_shadow = $meta->form_dark_input_box_shadow;
        $this->form_dark_input_border = $meta->form_dark_input_border;
        $this->form_dark_border_radius = $meta->form_dark_border_radius;
    }

}
