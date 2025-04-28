<?php

namespace App\Entity\Meta;

// all variables must have a default value
class ProjectMeta
{

    /**
     * Template variables
     */
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

    /**
     * Signup Form
     */
    public ?string $form_title = null;
    public ?string $form_description = null;
    public ?string $form_footer_text = null;
    public ?string $form_button_text = null;
    public ?string $form_success_message = null;

}
