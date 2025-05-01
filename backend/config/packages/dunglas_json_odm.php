<?php


use App\Entity\Meta\ProjectMeta;
use App\Entity\Type\ProjectFormDefaultColorPalette;

return static function (\Symfony\Config\DunglasDoctrineJsonOdmConfig $config): void {

    $config->typeMap('projects_meta', ProjectMeta::class);
    $config->typeMap('projects_meta_form_default_color_palette', ProjectFormDefaultColorPalette::class);

};
