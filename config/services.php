<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\DiscoveryController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\JwksController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OAuth2\IdTokenGrant;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect\IdTokenResponse;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Repository\IdentityProvider;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('ajgarlag.openid_connect_provider.repository.identity_provider', IdentityProvider::class)
            ->args([service(EventDispatcherInterface::class)])
        ->alias(IdentityProviderInterface::class, 'ajgarlag.openid_connect_provider.repository.identity_provider')
        ->alias(IdentityProvider::class, 'ajgarlag.openid_connect_provider.repository.identity_provider')

        ->set('ajgarlag.openid_connect_provider.openid_connect.claim_extractor', ClaimExtractor::class)

        ->set('ajgarlag.openid_connect_provider.openid_connect.response', IdTokenResponse::class)
            ->args([
                service('ajgarlag.openid_connect_provider.repository.identity_provider'),
                service('ajgarlag.openid_connect_provider.openid_connect.claim_extractor'),
                service(EventDispatcherInterface::class),
                service(RequestStack::class),
            ])
        ->alias(IdTokenResponse::class, 'ajgarlag.openid_connect_provider.openid_connect.response')

        ->set('ajgarlag.openid_connect_provider.grant.id_token', IdTokenGrant::class)
            ->args([
                service('ajgarlag.openid_connect_provider.openid_connect.response'),
                null,
            ])
        ->alias(IdTokenGrant::class, 'ajgarlag.openid_connect_provider.grant.id_token')

        ->set('ajgarlag.openid_connect_provider.controller.discovery', DiscoveryController::class)
            ->args([
                service('league.oauth2_server.authorization_server'),
                service(UrlGeneratorInterface::class),
                null,
                null,
                null,
                null,
            ])
            ->tag('controller.service_arguments')
        ->alias(DiscoveryController::class, 'ajgarlag.openid_connect_provider.controller.discovery')

        ->set('ajgarlag.openid_connect_provider.controller.jwks', JwksController::class)
            ->args([
                null,
            ])
            ->tag('controller.service_arguments')
        ->alias(JwksController::class, 'ajgarlag.openid_connect_provider.controller.jwks')

    ;
};
