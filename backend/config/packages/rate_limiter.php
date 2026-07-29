<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'framework' => [
        'rate_limiter' => [
            'public_api' => [
                'policy' => 'fixed_window',
                'limit' => 30,
                'interval' => '1 minute',
            ],
        ],
    ],
]);
