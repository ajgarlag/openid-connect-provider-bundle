<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('oidc_discovery', '.well-known/openid-configuration')
        ->controller(['ajgarlag.oidc_provider.controller.discovery', '__invoke'])
        ->methods(['GET'])

        ->add('oidc_jwks', '/jwks')
        ->controller(['ajgarlag.oidc_provider.controller.jwks', '__invoke'])
        ->methods(['GET'])
    ;
};
