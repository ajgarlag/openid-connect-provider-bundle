<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AvailableScopesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $availableScopes = [];
        $configs = $container->getExtensionConfig('league_oauth2_server');
        foreach (array_reverse($configs) as $config) {
            if (isset($config['scopes']['available'])) {
                array_push($availableScopes, ...$config['scopes']['available']);
            }
        }
        $availableScopes = array_unique($availableScopes);

        $container->getDefinition('ajgarlag.openid_connect_provider.provider_metadata_listener.core_recommended')
            ->replaceArgument(1, $availableScopes)
        ;
    }
}
