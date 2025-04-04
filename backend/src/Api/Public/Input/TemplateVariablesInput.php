<?php

namespace App\Api\Public\Input;

use App\Service\Template\TemplateDefaults;
use Symfony\Component\Validator\Constraints as Assert;

class TemplateVariablesInput
{
    #[Assert\Type('string')]
    public string $lang = TemplateDefaults::lang;

    #[Assert\Type('string')]
    public string $subject = TemplateDefaults::subject;

    #[Assert\Type('string')]
    public string $content = TemplateDefaults::content;

    public string $colorAccent = TemplateDefaults::COLOR_ACCENT;

    public string $colorBackground = TemplateDefaults::COLOR_BACKGROUND;

    public string $colorBoxBackground = TemplateDefaults::COLOR_BOX_BACKGROUND;

    #[Assert\Regex('/^\\d+px$/', message: 'Border radius must be in px (e.g., 5px)')]
    public string $colorBoxRadius = TemplateDefaults::COLOR_BOX_RADIUS;

    #[Assert\Regex('/^0 0 \d+px rgba\(0, 0, 0, 0\.\d+\)$/', message: 'Invalid box shadow format')]
    public string $colorBoxShadow = TemplateDefaults::COLOR_BOX_SHADOW;

    #[Assert\Regex('/^\d+px solid #[0-9a-fA-F]{6}$/', message: 'Invalid border format')]
    public string $colorBoxBorder = TemplateDefaults::COLOR_BOX_BORDER;

    #[Assert\Type('string')]
    public string $fontFamily = TemplateDefaults::FONT_FAMILY;

    #[Assert\Regex('/^\d+px$/', message: 'Font size must be in px (e.g., 16px)')]
    public string $fontSize = TemplateDefaults::FONT_SIZE;

    #[Assert\Choice(['normal', 'bold', 'bolder', 'lighter'])]
    public string $fontWeight = TemplateDefaults::FONT_WEIGHT;

    #[Assert\Choice(['normal', 'bold', 'bolder', 'lighter'])]
    public string $fontWeightHeading = TemplateDefaults::FONT_WEIGHT_HEADING;

    public string $fontColorOnBackground = TemplateDefaults::FONT_COLOR_ON_BACKGROUND;

    public string $fontColorOnBox = TemplateDefaults::FONT_COLOR_ON_BOX;

    #[Assert\Regex('/^\d+(\.\d+)?$/', message: 'Line height must be a number')]
    public string $fontLineHeight = TemplateDefaults::FONT_LINE_HEIGHT;
}
