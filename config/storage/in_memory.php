<?php

declare(strict_types=1);

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\InMemory\RelyingPartyManager;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\RelyingPartyManagerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('ajgarlag.openid_connect_provider.manager.in_memory.relying_party', RelyingPartyManager::class)
        ->alias(RelyingPartyManagerInterface::class, 'ajgarlag.openid_connect_provider.manager.in_memory.relying_party')
        ->alias(RelyingPartyManager::class, 'ajgarlag.openid_connect_provider.manager.in_memory.relying_party')

    ;
};
