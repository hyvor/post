<?php

namespace App\Entity\Meta;

// all variables must have a default value
use App\Entity\Type\NewsletterFormDefaultColorPalette;

class NewsletterMeta
{

    public ?string $logo = null;
    public ?string $address = null;
    public ?string $unsubscribe_text = null;
    public bool $branding = true;

    /**
     * Template variables
     */
    public ?string $template_color_accent = null;
    public ?string $template_color_accent_text = null;
    public ?string $template_color_background = null;
    public ?string $template_color_background_text = null;
    public ?string $template_color_box = null;
    public ?string $template_color_box_text = null;

    public ?string $template_box_shadow = null;
    public ?string $template_box_radius = null;
    public ?string $template_box_border = null;

    public ?string $template_font_family = null;
    public ?string $template_font_size = null;
    public ?string $template_font_weight = null;
    public ?string $template_font_weight_heading = null;
    public ?string $template_font_line_height = null;

    /**
     * Signup Form
     */
    public ?string $form_title = null;
    public ?string $form_description = null;
    public ?string $form_footer_text = null;
    public ?string $form_button_text = null;
    public ?string $form_success_message = null;

    public ?int $form_width = 425; // null = 100%
    public ?string $form_custom_css = null;

    public ?string $form_color_light_text = null; // null = inherit
    public ?string $form_color_light_text_light = null;
    public ?string $form_color_light_accent = null;
    public ?string $form_color_light_accent_text = null;
    public ?string $form_color_light_input = null;
    public ?string $form_color_light_input_text = null;
    public ?string $form_light_input_box_shadow = null;
    public ?string $form_light_input_border = null;
    public ?int $form_light_border_radius = null;

    public ?string $form_color_dark_text = null; // null = inherit
    public ?string $form_color_dark_text_light = null;
    public ?string $form_color_dark_accent = null;
    public ?string $form_color_dark_accent_text = null;
    public ?string $form_color_dark_input = null;
    public ?string $form_color_dark_input_text = null;
    public ?string $form_dark_input_box_shadow = null;
    public ?string $form_dark_input_border = null;
    public ?int $form_dark_border_radius = null;
    public NewsletterFormDefaultColorPalette $form_default_color_palette = NewsletterFormDefaultColorPalette::LIGHT;
    public int $form_input_border_radius = 20;

}
