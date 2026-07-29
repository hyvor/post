<?php

use App\Entity\Meta\NewsletterMeta;
use App\Entity\Type\NewsletterFormDefaultColorPalette;
use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'dunglas_doctrine_json_odm' => [
        'type_map' => [
            'newsletters_meta' => NewsletterMeta::class,
            'newsletters_meta_form_default_color_palette' => NewsletterFormDefaultColorPalette::class,
        ],
    ],
]);
