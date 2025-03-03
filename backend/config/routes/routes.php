<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {

    // console API
    $routes->import('../../src/Api/Console/Controller', 'attribute')
        ->prefix('/api/console')
        ->namePrefix('api_console_');

    // test API
    $routes->import('../../src/Api/Test', 'attribute')
        ->prefix('/api/test')
        ->namePrefix('api_test_');

};