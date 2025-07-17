<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class AjgarlagOpenIDConnectProviderExtension extends Extension
{
    public function getAlias(): string
    {
        return 'ajgarlag_openid_connect_provider';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.php');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->configureDiscovery($container, $config['discovery']);
    }

    /**
     * @param mixed[] $config
     */
    private function configureDiscovery(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition('ajgarlag.openid_connect_provider.controller.discovery')
            ->replaceArgument(2, $config['authorization_endpoint_route'])
            ->replaceArgument(3, $config['token_endpoint_route'])
            ->replaceArgument(4, $config['jwks_endpoint_route'])
            ->replaceArgument(5, $config['end_session_endpoint_route'])
        ;
    }
}
