<?php

declare(strict_types=1);

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientDataManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\InMemory\ClientDataManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('ajgarlag.openid_connect_provider.manager.in_memory.client_data', ClientDataManager::class)
        ->alias(ClientDataManagerInterface::class, 'ajgarlag.openid_connect_provider.manager.in_memory.client_data')
        ->alias(ClientDataManager::class, 'ajgarlag.openid_connect_provider.manager.in_memory.client_data')

    ;
};
