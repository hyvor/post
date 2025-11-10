<?php

namespace App\Api\Public\Object\Archive;

use App\Entity\Meta\NewsletterMeta;
use App\Service\Newsletter\NewsletterDefaults;

class TemplatePaletteObject
{
    public string $accent;
    public string $accent_text;
    public string $background;
    public string $background_text;
    public string $box;
    public string $box_text;

    public string $box_radius;
    public string $box_shadow;
    public string $box_border;

    public string $font_family;
    public string $font_size;
    public string $font_weight;
    public string $font_weight_heading;
    public string $font_line_height;

    public function __construct(NewsletterMeta $meta)
    {
        $this->accent = $meta->template_color_accent ?? NewsletterDefaults::TEMPLATE_COLOR_ACCENT;
        $this->accent_text = $meta->template_color_accent_text ?? NewsletterDefaults::TEMPLATE_COLOR_ACCENT_TEXT;
        $this->background = $meta->template_color_background ?? NewsletterDefaults::TEMPLATE_COLOR_BACKGROUND;
        $this->background_text = $meta->template_color_background_text ?? NewsletterDefaults::TEMPLATE_COLOR_BACKGROUND_TEXT;
        $this->box = $meta->template_color_box ?? NewsletterDefaults::TEMPLATE_COLOR_BOX;
        $this->box_text = $meta->template_color_box_text ?? NewsletterDefaults::TEMPLATE_COLOR_BOX_TEXT;

        $this->box_radius = $meta->template_box_radius ?? NewsletterDefaults::TEMPLATE_BOX_RADIUS;
        $this->box_shadow = $meta->template_box_shadow ?? NewsletterDefaults::TEMPLATE_BOX_SHADOW;
        $this->box_border = $meta->template_box_border ?? NewsletterDefaults::TEMPLATE_BOX_BORDER;

        $this->font_family = $meta->template_font_family ?? NewsletterDefaults::TEMPLATE_FONT_FAMILY;
        $this->font_size = $meta->template_font_size ?? NewsletterDefaults::TEMPLATE_FONT_SIZE;
        $this->font_weight = $meta->template_font_weight ?? NewsletterDefaults::TEMPLATE_FONT_WEIGHT;
        $this->font_weight_heading = $meta->template_font_weight_heading ?? NewsletterDefaults::TEMPLATE_FONT_WEIGHT_HEADING;
        $this->font_line_height = $meta->template_font_line_height ?? NewsletterDefaults::TEMPLATE_FONT_LINE_HEIGHT;
    }
}
