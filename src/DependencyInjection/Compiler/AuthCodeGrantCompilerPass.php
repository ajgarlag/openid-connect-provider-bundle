<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OAuth2\AuthCodeGrant;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestStack;

final class AuthCodeGrantCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $authCodeGrantDefinition = $container->getDefinition('league.oauth2_server.grant.auth_code');
        $authCodeGrantDefinition->setClass(AuthCodeGrant::class)
            ->setArgument(3, new Reference(RequestStack::class))
            ->setArgument(4, new Reference(ResponseFactoryInterface::class))
            ->setArgument(5, new Reference(UriFactoryInterface::class))
        ;
    }
}
