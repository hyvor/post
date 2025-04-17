<?php

namespace App\Api\Console\Input\Project;

use App\Util\OptionalPropertyTrait;

class UpdateProjectMetaInput
{
    use OptionalPropertyTrait;

    public ?string $templateColorAccent = null;
    public ?string $templateColorBackground = null;
    public ?string $templateColorBoxBackground = null;
    public ?string $templateColorBoxShadow = null;
    public ?string $templateColorBoxBorder = null;
    public ?string $templateFontFamily = null;
    public ?string $templateFontSize = null;
    public ?string $templateFontWeight = null;
    public ?string $templateFontWeightHeading = null;
    public ?string $templateFontColorOnBackground = null;
    public ?string $templateFontColorOnBox = null;
    public ?string $templateFontLineHeight = null;
    public ?string $templateBoxRadius = null;
    public ?string $templateLogo = null;
}
