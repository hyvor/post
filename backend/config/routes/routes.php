<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {


    // user API
    // Todo;

    // resource API
    $routes->import('../../src/Api/Resource/Controller', 'attribute')
        ->prefix('/api/resource')
        ->namePrefix('api_resource_');

};