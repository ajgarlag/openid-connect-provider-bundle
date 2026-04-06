<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ajgarlag_openid_connect_provider');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->append($this->createDiscoveryNode());
        $rootNode->append($this->createEndSessionNode());
        $rootNode->append($this->createAuthorizationServerNode());

        return $treeBuilder;
    }

    private function createDiscoveryNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('discovery');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('authorization_endpoint_route')
                    ->info('Route name for the authorization endpoint')
                    ->defaultValue('oauth2_authorize')
                ->end()
                ->scalarNode('token_endpoint_route')
                    ->info('Route name for the token endpoint')
                    ->defaultValue('oauth2_token')
                ->end()
                ->scalarNode('jwks_endpoint_route')
                    ->info('Route name for the jwks endpoint')
                    ->defaultValue('openid_connect_jwks')
                ->end()
                ->scalarNode('end_session_endpoint_route')
                    ->info('Route name for the end session endpoint')
                    ->defaultValue('openid_connect_end_session')
                ->end()
            ->end()
        ;

        return $node;
    }

    private function createEndSessionNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('end_session');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('cancel_logout_default_path')
                    ->info('URL or route names to redirect user on session ending if no post logout redirect URI is given.')
                    ->defaultValue('/')
                ->end()
            ->end()
        ;

        return $node;
    }

    private function createAuthorizationServerNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('authorization_server');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('refresh_token_require_offline_access_scope')
                    ->info('Whether refresh tokens require the offline_access scope to be requested. If true, refresh tokens will not be issued or will be issued with immediate expiry if offline_access is not requested.')
                    ->defaultFalse()
                ->end()
            ->end()
        ;

        return $node;
    }
}
