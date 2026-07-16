<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\NullLogger;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('nyholm.psr7.psr17_factory', Psr17Factory::class)
            ->alias(ResponseFactoryInterface::class, 'nyholm.psr7.psr17_factory')
            ->alias(UriFactoryInterface::class, 'nyholm.psr7.psr17_factory')

        ->set('logger', NullLogger::class)
    ;
};
