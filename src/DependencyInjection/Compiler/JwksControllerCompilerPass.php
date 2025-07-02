<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class JwksControllerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $resourceServerDefinition = $container->getDefinition('league.oauth2_server.resource_server');
        $privateKeyArgument = $resourceServerDefinition->getArgument(1);
        $jwksControllerDefinition = $container->getDefinition('ajgarlag.oidc_provider.controller.jwks');
        $jwksControllerDefinition->setArgument(0, $privateKeyArgument);
    }
}
