<?php

namespace App\Service\Project\Dto;

use App\Util\OptionalPropertyTrait;

class UpdateProjectMetaDto
{
    use OptionalPropertyTrait;

    public string $templateColorAccent;
    public string $templateColorBackground;
    public string $templateColorBoxBackground;
    public string $templateColorBoxShadow;
    public string $templateColorBoxBorder;
    public string $templateFontFamily;
    public string $templateFontSize;
    public string $templateFontWeight;
    public string $templateFontWeightHeading;
    public string $templateFontColorOnBackground;
    public string $templateFontColorOnBox;
    public string $templateFontLineHeight;
    public string $templateBoxRadius;
    public string $templateLogo;
}
