<?php

namespace App\Service\Template;

// https://post.hyvor.com/docs/email-templates
use App\Entity\Newsletter;
use App\Service\Newsletter\NewsletterDefaults;

class TemplateVariables
{

    public function __construct(
        // language code to be used in <html> tag
        public string $lang = NewsletterDefaults::TEMPLATE_LANG,

        // subject of the email to be used in <title> tag
        public string $subject = '',

        // content in HTML format
        public string $content = '',

        // header
        public string $name = '',
        public string $subdomain = '',
        public string $logo = '',
        public string $logo_url = '',

        // footer
        public string $address = '',
        public string $unsubscribe_url = '',
        public string $unsubscribe_text = '',
        public bool $branding = true,

        // colors in HEX format
        public string $color_accent = NewsletterDefaults::TEMPLATE_COLOR_ACCENT,
        public string $color_accent_text = NewsletterDefaults::TEMPLATE_COLOR_ACCENT_TEXT,
        public string $color_background = NewsletterDefaults::TEMPLATE_COLOR_BACKGROUND,
        public string $color_background_text = NewsletterDefaults::TEMPLATE_COLOR_BACKGROUND_TEXT,
        public string $color_box = NewsletterDefaults::TEMPLATE_COLOR_BOX,
        public string $color_box_text = NewsletterDefaults::TEMPLATE_COLOR_BOX_TEXT,

        // font
        public string $font_family = NewsletterDefaults::TEMPLATE_FONT_FAMILY,
        public string $font_size = NewsletterDefaults::TEMPLATE_FONT_SIZE,
        public string $font_weight = NewsletterDefaults::TEMPLATE_FONT_WEIGHT,
        public string $font_weight_heading = NewsletterDefaults::TEMPLATE_FONT_WEIGHT_HEADING,
        public string $font_line_height = NewsletterDefaults::TEMPLATE_FONT_LINE_HEIGHT,

        // Box radius
        public string $box_radius = NewsletterDefaults::TEMPLATE_BOX_RADIUS,
        public string $box_shadow = NewsletterDefaults::TEMPLATE_BOX_SHADOW,
        public string $box_border = NewsletterDefaults::TEMPLATE_BOX_BORDER,
    ) {
    }

}
