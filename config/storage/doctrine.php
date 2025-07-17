<?php

declare(strict_types=1);

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientExtensionManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\Doctrine\ClientExtensionManager;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Persistence\Mapping\Driver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('ajgarlag.openid_connect_provider.persistence.driver', Driver::class)
            ->args([
                'openid_connect_',
            ])
        ->alias(Driver::class, 'ajgarlag.openid_connect_provider.persistence.driver')

        ->set('ajgarlag.openid_connect_provider.manager.doctrine.client_extension', ClientExtensionManager::class)
            ->args([
                null,
            ])
        ->alias(ClientExtensionManagerInterface::class, 'ajgarlag.openid_connect_provider.manager.doctrine.client_extension')
        ->alias(ClientExtensionManager::class, 'ajgarlag.openid_connect_provider.manager.doctrine.client_extension')
    ;
};
