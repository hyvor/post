<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Api\Console\Resolver\NewsletterResolver;
use App\Api\Console\Resolver\EntityResolver;
use App\Api\Sudo\Resolver\EntityResolver as SudoEntityResolver;
use App\Service\Media\FilesystemFactory;
use Aws\S3\S3Client;
use League\Flysystem\Filesystem;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // ================ DEFAULTS =================

    // Default configurdevation for services
    $services->defaults()
        ->autowire(true)      // Automatically injects dependencies in your services.
        ->autoconfigure(true); // Automatically registers your services as commands, event subscribers, etc.

    // Makes classes in src/ available to be used as services
    // This creates a service per class whose id is the fully-qualified class name
    $services->load('App\\', '../src/')
        ->exclude([
            '../src/DependencyInjection/',
            '../src/Entity/',
            '../src/Kernel.php',
        ]);

    // ================ CONSOLE API =================
    $services->set(NewsletterResolver::class)
        ->tag(
            'controller.argument_value_resolver',
            ['name' => 'console_api_newsletter', 'priority' => 150]
        );
    $services->set(EntityResolver::class)
        ->tag(
            'controller.argument_value_resolver',
            ['name' => 'console_api_resource', 'priority' => 150]
        );

    // ================ SUDO API =================
    $services->set(SudoEntityResolver::class)
        ->tag(
            'controller.argument_value_resolver',
            ['name' => 'sudo_api_resource', 'priority' => 150]
        );

    // ================ STORAGE =================
    $services->set(S3Client::class)
        ->arg('$args', [
            'version' => 'latest',
            'region' => '%env(S3_REGION)%',
            'endpoint' => '%env(S3_ENDPOINT)%',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => '%env(S3_ACCESS_KEY_ID)%',
                'secret' => '%env(S3_SECRET_ACCESS_KEY)%'
            ]
        ]);

    $services->set(Filesystem::class)
        ->factory([FilesystemFactory::class, 'create'])
        ->args([
            '%env(FILESYSTEM_ADAPTER)%',
            service(S3Client::class),
            '%env(S3_BUCKET)%',
            '%kernel.project_dir%/var/uploads',
        ]);
};
