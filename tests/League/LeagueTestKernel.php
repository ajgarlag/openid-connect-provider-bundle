<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\League;

use League\Bundle\OAuth2ServerBundle\Tests\TestKernel;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class LeagueTestKernel extends TestKernel
{
    public function registerBundles(): iterable
    {
        return [
            ...parent::registerBundles(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Ajgarlag\Bundle\OpenIDConnectProviderBundle\AjgarlagOpenIDConnectProviderBundle(),
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        $container->register('nyholm.psr7.psr17_factory', Psr17Factory::class);
        $container->setAlias(ResponseFactoryInterface::class, 'nyholm.psr7.psr17_factory');
        $container->setAlias(UriFactoryInterface::class, 'nyholm.psr7.psr17_factory');
    }
}
