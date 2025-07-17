<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection\Compiler;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Persistence\Mapping\Driver;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class StorageCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../../config/storage'));
        $ormCompilerPass = new DoctrineOrmMappingsPass(
            new Reference(Driver::class),
            ['Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model'],
            ['league.oauth2_server.persistence.doctrine.manager'],
            'league.oauth2_server.persistence.doctrine.enabled',
        );
        $ormCompilerPass->process($container);

        if ($container->hasParameter('league.oauth2_server.persistence.doctrine.enabled') && true === $container->getParameter('league.oauth2_server.persistence.doctrine.enabled')) {
            $loader->load('doctrine.php');
            $clientManagerDefinition = $container->getDefinition('ajgarlag.openid_connect_provider.manager.doctrine.client_data');
            $clientManagerDefinition->setArgument(0, new Reference(\sprintf('doctrine.orm.%s_entity_manager', $container->getParameter('league.oauth2_server.persistence.doctrine.manager')))); // @phpstan-ignore argument.type
        } else {
            $loader->load('in_memory.php');
        }
    }
}
