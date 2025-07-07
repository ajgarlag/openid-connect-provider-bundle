<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class IdTokenGrantCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $implicitGrantDefinition = $container->getDefinition('league.oauth2_server.grant.implicit');
        $idTokenGrantDefinition = $container->getDefinition('ajgarlag.openid_connect_provider.grant.id_token');
        $idTokenGrantDefinition->setArgument(1, $implicitGrantDefinition->getArgument(0));
    }
}
