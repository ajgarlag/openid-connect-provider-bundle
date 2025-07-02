<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class AjgarlagOidcProviderExtension extends Extension
{
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
        // foreach ($config as $key => $value) {
        //     $container->setParameter('ajgarlag.oidc_provider.discovery.'.$key, $value);
        // }
        $container->getDefinition('ajgarlag.oidc_provider.controller.discovery')
            ->replaceArgument(1, $config['authorization_endpoint_route'])
            ->replaceArgument(2, $config['token_endpoint_route'])
            ->replaceArgument(3, $config['jwks_endpoint_route'])
            ->replaceArgument(4, $config['response_types_supported'])
        ;
    }
}
