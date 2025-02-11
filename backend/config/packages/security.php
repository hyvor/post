<?php

// config/packages/security.php
use Hyvor\Internal\Bundle\Security\HyvorAuthenticator;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {

    // $security->enableAuthenticatorManager(true);
    // ....

    $security->firewall('main')
        ->customAuthenticators([HyvorAuthenticator::class]);
};