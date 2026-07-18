<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use League\Bundle\OAuth2ServerBundle\Tests\TestHelper;

return static function (ContainerConfigurator $container): void {
    $container->extension('league_oauth2_server', [
        'authorization_server' => [
            'private_key' => '%kernel.project_dir%/private.key',
            'encryption_key' => TestHelper::ENCRYPTION_KEY,
            'enable_password_grant' => false,
            'enable_implicit_grant' => true,
        ],
        'resource_server' => [
            'public_key' => '%kernel.project_dir%/public.key',
        ],
        'scopes' => [
            'available' => [
                'openid',
            ],
            'default' => [
                'openid',
            ],
        ],
        'persistence' => [
            'in_memory' => null,
        ],
        'client' => [
            'allow_plaintext_secrets' => false,
        ],
    ]);
};
