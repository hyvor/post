<?php


use App\Entity\Meta\NewsletterMeta;
use App\Entity\Type\NewsletterFormDefaultColorPalette;

return static function (\Symfony\Config\DunglasDoctrineJsonOdmConfig $config): void {

    $config->typeMap('newsletters_meta', NewsletterMeta::class);
    $config->typeMap('newsletters_meta_form_default_color_palette', NewsletterFormDefaultColorPalette::class);

};
