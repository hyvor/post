<?php

use App\Service\Sudo\SudoPermission;
use App\Service\Sudo\SudoRole;
use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'internal' => [
        'component' => 'post',
        'i18n' => [
            'default' => 'en',
        ],
        'sudo' => [
            'permission_enum' => SudoPermission::class,
            'role_enum' => SudoRole::class,
        ]
    ],
]);
