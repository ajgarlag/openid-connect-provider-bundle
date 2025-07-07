<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler;

use League\Bundle\OAuth2ServerBundle\Manager\ScopeManagerInterface;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class OpenIdScopeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $scopeManagerDefinition = $container->findDefinition(ScopeManagerInterface::class);

        $scopeManagerDefinition->addMethodCall('save', [
            new Definition(Scope::class, ['openid']),
        ]);
    }
}
