<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {

    // console API
    $routes->import('../../src/Api/Console/Controller', 'attribute')
        ->prefix('/api/console')
        ->namePrefix('api_console_');

    // public API
    $routes->import('../../src/Api/Public/Controller', 'attribute')
        ->prefix('/api/public')
        ->namePrefix('api_public_');

    $routes->import('../../src/Api/Local', 'attribute')
        ->prefix('/api/local')
        ->condition('env("APP_ENV") in ["dev", "test"]')
        ->namePrefix('api_local_');

};