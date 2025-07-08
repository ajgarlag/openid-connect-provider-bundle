<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\AjgarlagOpenIDConnectProviderExtension;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler\AuthCodeGrantCompilerPass;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler\AuthorizationServerCompilerPass;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler\EndSessionControllerCompilerPass;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler\IdTokenGrantCompilerPass;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler\JwksControllerCompilerPass;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler\OpenIdScopeCompilerPass;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler\StorageCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AjgarlagOpenIDConnectProviderBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new AjgarlagOpenIDConnectProviderExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AuthCodeGrantCompilerPass())
            ->addCompilerPass(new AuthorizationServerCompilerPass())
            ->addCompilerPass(new EndSessionControllerCompilerPass())
            ->addCompilerPass(new IdTokenGrantCompilerPass())
            ->addCompilerPass(new JwksControllerCompilerPass())
            ->addCompilerPass(new OpenIdScopeCompilerPass())
            ->addCompilerPass(new StorageCompilerPass())
        ;
    }

    public function getPath(): string
    {
        $reflected = new \ReflectionObject($this);

        return \dirname($reflected->getFileName() ?: __FILE__, 2);
    }
}
