<?php

declare(strict_types=1);

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    if (Kernel::VERSION_ID < 80200) {
        $routes->import('security.route_loader.logout', 'service');
    } else {
        $routes->import('security.route_loader', 'service');
    }
};
