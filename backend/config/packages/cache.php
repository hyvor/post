<?php

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {

    $framework->cache()
        ->app('cache.adapter.doctrine_dbal')
        ->defaultDoctrineDbalProvider('doctrine.dbal.default_connection');

};