<?php

namespace App\Service\EmailTemplate;

// https://post.hyvor.com/docs/email-templates
use App\Service\Project\ProjectDefaults;

class EmailTemplateVariables
{

    public function __construct(
        // language code to be used in <html> tag
        public string $lang = ProjectDefaults::TEMPLATE_LANG,

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
        public string $color_accent = ProjectDefaults::TEMPLATE_COLOR_ACCENT,
        public string $color_background = ProjectDefaults::TEMPLATE_COLOR_BACKGROUND,
        public string $color_box_background = ProjectDefaults::TEMPLATE_COLOR_BOX_BACKGROUND,

        // font
        public string $font_family = ProjectDefaults::TEMPLATE_FONT_FAMILY,
        public string $font_size = ProjectDefaults::TEMPLATE_FONT_SIZE,
        public string $font_weight = ProjectDefaults::TEMPLATE_FONT_WEIGHT,
        public string $font_weight_heading = ProjectDefaults::TEMPLATE_FONT_WEIGHT_HEADING,
        public string $font_color_on_background = ProjectDefaults::TEMPLATE_FONT_COLOR_ON_BACKGROUND,
        public string $font_color_on_box = ProjectDefaults::TEMPLATE_FONT_COLOR_ON_BOX,
        public string $font_line_height = ProjectDefaults::TEMPLATE_FONT_LINE_HEIGHT,

        // Box radius
        public string $box_radius = ProjectDefaults::TEMPLATE_BOX_RADIUS,
        public string $box_shadow = ProjectDefaults::TEMPLATE_BOX_SHADOW,
        public string $box_border = ProjectDefaults::TEMPLATE_BOX_BORDER,
    ) {
    }

}
