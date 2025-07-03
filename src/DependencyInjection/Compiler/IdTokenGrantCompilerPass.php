<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class IdTokenGrantCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $implicitGrantDefinition = $container->getDefinition('league.oauth2_server.grant.implicit');
        $idTokenGrantDefinition = $container->getDefinition('ajgarlag.oidc_provider.grant.id_token');
        $idTokenGrantDefinition->setArgument(1, $implicitGrantDefinition->getArgument(0));
    }
}
