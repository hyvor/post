<?php


use App\Entity\Meta\NewsletterMeta;
use App\Entity\Type\NewsletterFormDefaultColorPalette;

return static function (\Symfony\Config\DunglasDoctrineJsonOdmConfig $config): void {

    $config->typeMap('projects_meta', NewsletterMeta::class);
    $config->typeMap('projects_meta_form_default_color_palette', NewsletterFormDefaultColorPalette::class);

};
