<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->import(__DIR__ . '/../../vendor/league/oauth2-server-bundle/tests/Fixtures/routes.php');
    $routes->import('@AjgarlagOpenIDConnectProviderBundle/config/routes.php');
};
