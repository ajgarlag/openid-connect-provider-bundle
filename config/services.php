<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Command\SaveRelyingPartyCommand;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Command\ShowRelyingPartyCommand;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\DiscoveryController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\EndSessionController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\FrontChannelLogoutController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\JwksController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener\FrontChannelLogoutRedirectListener;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener\PostLogoutRedirectListener;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener\TrackLoggedInRelyingPartyListener;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\CacheLoggedInRelyingPartyStorage;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\CachePostLogoutRedirectUriStorage;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\LoggedInRelyingPartyStorageInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\PostLogoutRedirectUriStorageInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\RelyingPartyManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OAuth2\IdTokenGrant;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect\IdTokenResponse;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect\SessionSidManager;
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

        ->set('ajgarlag.openid_connect_provider.openid_connect.session_sid_manager', SessionSidManager::class)
            ->args([
                service(RequestStack::class),
                service('security.helper'),
            ])

        ->set('ajgarlag.openid_connect_provider.openid_connect.response', IdTokenResponse::class)
            ->args([
                service('ajgarlag.openid_connect_provider.repository.identity_provider'),
                service('ajgarlag.openid_connect_provider.openid_connect.claim_extractor'),
                service(EventDispatcherInterface::class),
                service(RequestStack::class),
                service('ajgarlag.openid_connect_provider.openid_connect.session_sid_manager'),
            ])
        ->alias(IdTokenResponse::class, 'ajgarlag.openid_connect_provider.openid_connect.response')

        ->set('ajgarlag.openid_connect_provider.grant.id_token', IdTokenGrant::class)
            ->args([
                service('ajgarlag.openid_connect_provider.openid_connect.response'),
                null,
            ])
        ->alias(IdTokenGrant::class, 'ajgarlag.openid_connect_provider.grant.id_token')

        ->set('ajgarlag.openid_connect_provider.listener.front_channel_logout_redirect', FrontChannelLogoutRedirectListener::class)
            ->args([
                service(PostLogoutRedirectUriStorageInterface::class),
                service('security.helper'),
                service(UrlGeneratorInterface::class),
                service('uri_signer'),
                service('ajgarlag.openid_connect_provider.openid_connect.session_sid_manager'),
                service('security.http_utils'),
                service(LoggedInRelyingPartyStorageInterface::class),
                'openid_connect_front_channel_logout',
                '/',
            ])
            ->tag('kernel.event_subscriber')
        ->alias(FrontChannelLogoutRedirectListener::class, 'ajgarlag.openid_connect_provider.listener.front_channel_logout_redirect')

        ->set('ajgarlag.openid_connect_provider.listener.post_logout_redirect', PostLogoutRedirectListener::class)
            ->args([
                service(PostLogoutRedirectUriStorageInterface::class),
                service('security.helper'),
            ])
            ->tag('kernel.event_subscriber')
        ->alias(PostLogoutRedirectListener::class, 'ajgarlag.openid_connect_provider.listener.post_logout_redirect')

        ->set('ajgarlag.openid_connect_provider.listener.track_logged_in_relying_party', TrackLoggedInRelyingPartyListener::class)
            ->args([
                service(RequestStack::class),
                service(LoggedInRelyingPartyStorageInterface::class),
            ])
            ->tag('kernel.event_subscriber')
        ->alias(TrackLoggedInRelyingPartyListener::class, 'ajgarlag.openid_connect_provider.listener.track_logged_in_relying_party')

        ->set('ajgarlag.openid_connect_provider.command.show_relying_party', ShowRelyingPartyCommand::class)
            ->args([
                service(ClientManagerInterface::class),
                service(RelyingPartyManagerInterface::class),
            ])
            ->tag('console.command', ['command' => 'ajgarlag:openid-connect-provider:show-relying-party'])
        ->alias(ShowRelyingPartyCommand::class, 'ajgarlag.openid_connect_provider.command.show_relying_party')

        ->set('ajgarlag.openid_connect_provider.command.save_relying_party', SaveRelyingPartyCommand::class)
            ->args([
                service(ClientManagerInterface::class),
                service(RelyingPartyManagerInterface::class),
            ])
            ->tag('console.command', ['command' => 'ajgarlag:openid-connect-provider:save-relying-party'])
        ->alias(SaveRelyingPartyCommand::class, 'ajgarlag.openid_connect_provider.command.save_relying_party')

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
                service(RelyingPartyManagerInterface::class),
                null,
                service(PostLogoutRedirectUriStorageInterface::class),
                service('security.helper'),
                service('ajgarlag.openid_connect_provider.openid_connect.session_sid_manager'),
                service('twig'),
                service('security.http_utils'),
                null,
            ])
            ->tag('controller.service_arguments')
        ->alias(EndSessionController::class, 'ajgarlag.openid_connect_provider.controller.end_session')

        ->set('ajgarlag.openid_connect_provider.controller.front_channel_logout', FrontChannelLogoutController::class)
            ->args([
                service('uri_signer'),
                service(ClientManagerInterface::class),
                service(RelyingPartyManagerInterface::class),
                service('twig'),
                service('security.http_utils'),
                '/',
            ])
            ->tag('controller.service_arguments')
        ->alias(FrontChannelLogoutController::class, 'ajgarlag.openid_connect_provider.controller.front_channel_logout')

        ->set('ajgarlag.openid_connect_provider.controller.jwks', JwksController::class)
            ->args([
                null,
            ])
            ->tag('controller.service_arguments')
        ->alias(JwksController::class, 'ajgarlag.openid_connect_provider.controller.jwks')

        ->set('ajgarlag.openid_connect_provider.logout.logged_in_relying_party.cache', CacheLoggedInRelyingPartyStorage::class)
            ->args([
                service('cache.app'),
                86400,
            ])
        ->alias(CacheLoggedInRelyingPartyStorage::class, 'ajgarlag.openid_connect_provider.logout.logged_in_relying_party.cache')
        ->alias(LoggedInRelyingPartyStorageInterface::class, 'ajgarlag.openid_connect_provider.logout.logged_in_relying_party.cache')

        ->set('ajgarlag.openid_connect_provider.logout.post_logout_redirect_storage.cache', CachePostLogoutRedirectUriStorage::class)
            ->args([
                service('cache.app'),
                60,
            ])
        ->alias(CachePostLogoutRedirectUriStorage::class, 'ajgarlag.openid_connect_provider.logout.post_logout_redirect_storage.cache')
        ->alias(PostLogoutRedirectUriStorageInterface::class, 'ajgarlag.openid_connect_provider.logout.post_logout_redirect_storage.cache')
    ;
};
