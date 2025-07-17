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
    ;
};
