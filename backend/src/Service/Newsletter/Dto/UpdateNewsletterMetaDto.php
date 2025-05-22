<?php

namespace App\Service\Project\Dto;

use App\Util\OptionalPropertyTrait;

/**
 * compared to ProjectMeta, this is camelCase and does not have default values
 */
class UpdateProjectMetaDto
{
    use OptionalPropertyTrait;

    public ?string $templateColorAccent;
    public ?string $templateColorBackground;
    public ?string $templateColorBoxBackground;
    public ?string $templateColorBoxShadow;
    public ?string $templateColorBoxBorder;
    public ?string $templateFontFamily;
    public ?string $templateFontSize;
    public ?string $templateFontWeight;
    public ?string $templateFontWeightHeading;
    public ?string $templateFontColorOnBackground;
    public ?string $templateFontColorOnBox;
    public ?string $templateFontLineHeight;
    public ?string $templateBoxRadius;
    public ?string $templateLogo;

    // form
    public ?string $formTitle;
    public ?string $formDescription;
    public ?string $formFooterText;
    public ?string $formButtonText;
    public ?string $formSuccessMessage;
}
