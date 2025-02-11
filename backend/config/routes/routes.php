<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {

    // console API
    $routes->import('../../src/Api/Console/Controller', 'attribute')
        ->prefix('/api/console')
        ->namePrefix('api_console_');

};