<?php

use Hyvor\Internal\Bundle\Security\HyvorAuthenticator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Config\SecurityConfig;

return static function (ContainerBuilder $container, SecurityConfig $security): void {

    $security
        ->firewall('hyvor_auth')
        ->stateless(true)
        ->lazy(true)
        ->customAuthenticators([HyvorAuthenticator::class]);

    /*
    $security
        ->accessControl()
        ->path('^/api/console')
        ->roles(UserRole::USER);
    */

};
