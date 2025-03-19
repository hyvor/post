<?php

namespace App\Service\Template;

// https://post.hyvor.com/docs/email-templates
class TemplateVariables
{

    public function __construct(
        // language code to be used in <html> tag
        public string $lang,

        // subject of the email to be used in <title> tag
        public string $subject,

        // content in HTML format
        public string $content,

        // header
        public string $logo,
        public string $logo_alt,
        public string $brand,
        public string $brand_url,

        // footer
        public string $address,
        public string $unsubscribe_url,
        public string $unsubscribe_text,

        // colors in HEX format
        public string $color_accent,
        public string $color_background,
        public string $color_box_background,
        public string $color_box_radius,
        public string $color_box_shadow,
        public string $color_box_border,

        // font
        public string $font_family,
        public string $font_size,
        public string $font_weight,
        public string $font_weight_heading,
        public string $font_color_on_background,
        public string $font_color_on_box,
        public string $font_line_height,
    )
    {
    }

}