<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests;

use League\Bundle\OAuth2ServerBundle\Tests\TestKernel as LeagueTestKernel;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TestKernel extends LeagueTestKernel
{
    public function registerBundles(): iterable
    {
        return [
            ...parent::registerBundles(),
            new \Ajgarlag\Bundle\OpenIDConnectProviderBundle\AjgarlagOpenIDConnectProviderBundle(),
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        $container->register('nyholm.psr7.psr17_factory', Psr17Factory::class);
        $container->setAlias(ResponseFactoryInterface::class, 'nyholm.psr7.psr17_factory');
        $container->setAlias(UriFactoryInterface::class, 'nyholm.psr7.psr17_factory');
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('ajgarlag_openid_connect_provider', []);
            $container->loadFromExtension('framework', [
                'router' => [
                    'resource' => __DIR__ . '/Fixtures/routes.php',
                ],
            ]);
        });
    }
}
