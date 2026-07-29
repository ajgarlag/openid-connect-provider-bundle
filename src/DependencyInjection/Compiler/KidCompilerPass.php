<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class KidCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $resourceServer = $container->getDefinition('league.oauth2_server.resource_server');

        $container->getDefinition('ajgarlag.openid_connect_provider.kid')
            ->replaceArgument(0, $resourceServer->getArgument(1))
        ;
    }
}
