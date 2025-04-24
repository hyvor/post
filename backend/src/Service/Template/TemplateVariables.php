<?php

namespace App\Service\Template;

// https://post.hyvor.com/docs/email-templates
class TemplateVariables
{

    public function __construct(
        // language code to be used in <html> tag
        public string $lang = TemplateDefaults::LANG,

        // subject of the email to be used in <title> tag
        public string $subject = '',

        // content in HTML format
        public string $content = '',

        // header
        public string $logo = '',
        public string $logo_alt = '',
        public string $brand = '',
        public string $brand_url = '',

        // footer
        public string $address = '',
        public string $unsubscribe_url = '',
        public string $unsubscribe_text = '',

        // colors in HEX format
        public string $color_accent = TemplateDefaults::COLOR_ACCENT,
        public string $color_background = TemplateDefaults::COLOR_BACKGROUND,
        public string $color_box_background = TemplateDefaults::COLOR_BACKGROUND,

        // font
        public string $font_family = TemplateDefaults::FONT_FAMILY,
        public string $font_size = TemplateDefaults::FONT_SIZE,
        public string $font_weight = TemplateDefaults::FONT_WEIGHT,
        public string $font_weight_heading = TemplateDefaults::FONT_WEIGHT_HEADING,
        public string $font_color_on_background = TemplateDefaults::FONT_COLOR_ON_BACKGROUND,
        public string $font_color_on_box = TemplateDefaults::FONT_COLOR_ON_BOX,
        public string $font_line_height = TemplateDefaults::FONT_LINE_HEIGHT,

        // Box radius
        public string $box_radius = TemplateDefaults::BOX_RADIUS,
        public string $box_shadow = TemplateDefaults::BOX_SHADOW,
        public string $box_border = TemplateDefaults::BOX_BORDER,
    )
    {
    }

}
