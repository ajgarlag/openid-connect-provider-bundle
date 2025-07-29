<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Command\SaveClientDataCommand;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Command\ShowClientDataCommand;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\DiscoveryController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\EndSessionController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\JwksController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener\PostLogoutRedirectListener;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\CachePostLogoutRedirectUriStorage;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\PostLogoutRedirectUriStorageInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientDataManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OAuth2\IdTokenGrant;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect\IdTokenResponse;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Repository\IdentityProvider;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
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

         ->set('ajgarlag.openid_connect_provider.listener.post_logout_redirect', PostLogoutRedirectListener::class)
            ->args([
                service(PostLogoutRedirectUriStorageInterface::class),
                service('security.helper'),
            ])
            ->tag('kernel.event_subscriber')
        ->alias(PostLogoutRedirectListener::class, 'ajgarlag.openid_connect_provider.listener.post_logout_redirect')

        ->set('ajgarlag.openid_connect_provider.command.show_client_data', ShowClientDataCommand::class)
            ->args([
                service(ClientManagerInterface::class),
                service(ClientDataManagerInterface::class),
            ])
            ->tag('console.command', ['command' => 'ajgarlag:openid-connect-provider:show-client-data'])
        ->alias(ShowClientDataCommand::class, 'ajgarlag.openid_connect_provider.command.show_client_data')

        ->set('ajgarlag.openid_connect_provider.command.save_client_data', SaveClientDataCommand::class)
            ->args([
                service(ClientManagerInterface::class),
                service(ClientDataManagerInterface::class),
            ])
            ->tag('console.command', ['command' => 'ajgarlag:openid-connect-provider:save-client-data'])
        ->alias(SaveClientDataCommand::class, 'ajgarlag.openid_connect_provider.command.save_client_data')

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

        ->set('ajgarlag.openid_connect_provider.controller.end_session', EndSessionController::class)
            ->args([
                service('security.logout_url_generator'),
                service(ClientManagerInterface::class),
                service(ClientDataManagerInterface::class),
                null,
                service(PostLogoutRedirectUriStorageInterface::class),
                service('security.helper'),
                service('twig'),
                service('security.http_utils'),
                null,
            ])
            ->tag('controller.service_arguments')
        ->alias(EndSessionController::class, 'ajgarlag.openid_connect_provider.controller.end_session')

        ->set('ajgarlag.openid_connect_provider.controller.jwks', JwksController::class)
            ->args([
                null,
            ])
            ->tag('controller.service_arguments')
        ->alias(JwksController::class, 'ajgarlag.openid_connect_provider.controller.jwks')

        ->set('ajgarlag.openid_connect_provider.logout.post_logout_redirect_storage.cache', CachePostLogoutRedirectUriStorage::class)
            ->args([
                service('cache.app'),
                60,
            ])
        ->alias(CachePostLogoutRedirectUriStorage::class, 'ajgarlag.openid_connect_provider.logout.post_logout_redirect_storage.cache')
        ->alias(PostLogoutRedirectUriStorageInterface::class, 'ajgarlag.openid_connect_provider.logout.post_logout_redirect_storage.cache')
    ;
};
