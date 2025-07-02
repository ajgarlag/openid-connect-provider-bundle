<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle;

use Ajgarlag\Bundle\OidcProviderBundle\DependencyInjection\Compiler\AuthCodeGrantCompilerPass;
use Ajgarlag\Bundle\OidcProviderBundle\DependencyInjection\Compiler\AuthorizationServerCompilerPass;
use Ajgarlag\Bundle\OidcProviderBundle\DependencyInjection\Compiler\IdTokenGrantCompilerPass;
use Ajgarlag\Bundle\OidcProviderBundle\DependencyInjection\Compiler\JwksControllerCompilerPass;
use Ajgarlag\Bundle\OidcProviderBundle\DependencyInjection\Compiler\OpenIdScopeCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AjgarlagOidcProviderBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AuthCodeGrantCompilerPass())
            ->addCompilerPass(new AuthorizationServerCompilerPass())
            ->addCompilerPass(new IdTokenGrantCompilerPass())
            ->addCompilerPass(new JwksControllerCompilerPass())
            ->addCompilerPass(new OpenIdScopeCompilerPass())
        ;
    }

    public function getPath(): string
    {
        $reflected = new \ReflectionObject($this);

        return \dirname($reflected->getFileName() ?: __FILE__, 2);
    }
}
