<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EndSessionControllerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $resourceServerDefinition = $container->getDefinition('league.oauth2_server.resource_server');
        $privateKeyArgument = $resourceServerDefinition->getArgument(1);
        $jwksControllerDefinition = $container->getDefinition('ajgarlag.openid_connect_provider.controller.end_session');
        $jwksControllerDefinition->setArgument(3, $privateKeyArgument);
    }
}
