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

    // sudo API
    $routes->import('../../src/Api/Sudo/Controller', 'attribute')
        ->prefix('/api/sudo')
        ->namePrefix('api_sudo_');

    $routes->import('../../src/Api/Machine/Controller', 'attribute')
        ->prefix('/api/machine')
        ->namePrefix('api_machine_');

    // root API
    $routes->import('../../src/Api/Root', 'attribute')
        ->prefix('/api')
        ->namePrefix('api_root_');

    $routes->import('../../src/Api/Local', 'attribute')
        ->prefix('/api/local')
        ->condition('env("APP_ENV") in ["dev", "test"]')
        ->namePrefix('api_local_');
};
