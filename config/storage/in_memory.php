<?php

declare(strict_types=1);

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientExtensionManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\InMemory\ClientExtensionManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('ajgarlag.openid_connect_provider.manager.in_memory.client_extension', ClientExtensionManager::class)
        ->alias(ClientExtensionManagerInterface::class, 'ajgarlag.openid_connect_provider.manager.in_memory.client_extension')
        ->alias(ClientExtensionManager::class, 'ajgarlag.openid_connect_provider.manager.in_memory.client_extension')

    ;
};
