<?php

declare(strict_types=1);

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\Doctrine\RelyingPartyManager;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\RelyingPartyManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Persistence\Mapping\Driver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('ajgarlag.openid_connect_provider.persistence.driver', Driver::class)
            ->args([
                'openid_connect_',
            ])
        ->alias(Driver::class, 'ajgarlag.openid_connect_provider.persistence.driver')

        ->set('ajgarlag.openid_connect_provider.manager.doctrine.relying_party', RelyingPartyManager::class)
            ->args([
                null,
            ])
        ->alias(RelyingPartyManagerInterface::class, 'ajgarlag.openid_connect_provider.manager.doctrine.relying_party')
        ->alias(RelyingPartyManager::class, 'ajgarlag.openid_connect_provider.manager.doctrine.relying_party')
    ;
};
