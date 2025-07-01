<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle;

use Ajgarlag\Bundle\OidcProviderBundle\DependencyInjection\Compiler\AuthorizationServerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AjgarlagOidcProviderBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AuthorizationServerCompilerPass());
    }
}
