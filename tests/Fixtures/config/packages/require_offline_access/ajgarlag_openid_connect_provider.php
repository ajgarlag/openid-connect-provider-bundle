<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('ajgarlag_openid_connect_provider', [
        'authorization_server' => [
            'refresh_token_require_offline_access_scope' => true,
        ],
    ]);
};
