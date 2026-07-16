<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('security', [
        'providers' => [
            'users' => [
                'memory' => [
                    'users' => [
                        'user' => [
                            'roles' => ['ROLE_USER'],
                        ],
                    ],
                ],
            ],
        ],
        'firewalls' => [
            'token' => [
                'pattern' => '^/token',
                'security' => false,
            ],
            'main' => [
                'pattern' => '^/',
                'provider' => 'users',
                'form_login' => null,
                'logout' => null,
            ],
        ],
        'access_control' => [
            ['path' => '^/authorize', 'roles' => 'ROLE_USER'],
        ],
    ]);
};
