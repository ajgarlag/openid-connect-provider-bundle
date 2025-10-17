<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes
        ->add('openid_connect_discovery', '.well-known/openid-configuration')
        ->controller(['ajgarlag.openid_connect_provider.controller.discovery', '__invoke'])
        ->methods(['GET'])

        ->add('openid_connect_jwks', '/jwks')
        ->controller(['ajgarlag.openid_connect_provider.controller.jwks', '__invoke'])
        ->methods(['GET'])

        ->add('openid_connect_end_session', '/end-session')
        ->controller(['ajgarlag.openid_connect_provider.controller.end_session', '__invoke'])
        ->methods(['GET', 'POST'])

        ->add('openid_connect_front_channel_logout', '/front-channel-logout')
        ->controller(['ajgarlag.openid_connect_provider.controller.front_channel_logout', '__invoke'])
        ->methods(['GET'])
    ;
};
