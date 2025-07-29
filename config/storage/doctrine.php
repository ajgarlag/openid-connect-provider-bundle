<?php

declare(strict_types=1);

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientDataManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\Doctrine\ClientDataManager;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Persistence\Mapping\Driver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('ajgarlag.openid_connect_provider.persistence.driver', Driver::class)
            ->args([
                'openid_connect_',
            ])
        ->alias(Driver::class, 'ajgarlag.openid_connect_provider.persistence.driver')

        ->set('ajgarlag.openid_connect_provider.manager.doctrine.client_data', ClientDataManager::class)
            ->args([
                null,
            ])
        ->alias(ClientDataManagerInterface::class, 'ajgarlag.openid_connect_provider.manager.doctrine.client_data')
        ->alias(ClientDataManager::class, 'ajgarlag.openid_connect_provider.manager.doctrine.client_data')
    ;
};
