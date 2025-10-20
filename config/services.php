<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Command\SaveRelyingPartyCommand;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Command\ShowRelyingPartyCommand;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\DiscoveryController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\EndSessionController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\JwksController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener\PostLogoutRedirectListener;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\CachePostLogoutRedirectUriStorage;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\PostLogoutRedirectUriStorageInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\RelyingPartyManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OAuth2\IdTokenGrant;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect\IdTokenResponse;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Repository\IdentityProvider;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('ajgarlag.openid_connect_provider.repository.identity_provider', IdentityProvider::class)
            ->args([service('event_dispatcher')])
        ->alias(IdentityProviderInterface::class, 'ajgarlag.openid_connect_provider.repository.identity_provider')
        ->alias(IdentityProvider::class, 'ajgarlag.openid_connect_provider.repository.identity_provider')
            ->deprecate('ajgarlag/openid-connect-provider-bundle', '0.2.2', 'The "%alias_id%" alias is deprecated, use "ajgarlag.openid_connect_provider.repository.identity_provider" instead.')

        ->set('ajgarlag.openid_connect_provider.openid_connect.claim_extractor', ClaimExtractor::class)

        ->set('ajgarlag.openid_connect_provider.openid_connect.response', IdTokenResponse::class)
            ->args([
                service('ajgarlag.openid_connect_provider.repository.identity_provider'),
                service('ajgarlag.openid_connect_provider.openid_connect.claim_extractor'),
                service('event_dispatcher'),
                service('request_stack'),
            ])
        ->alias(IdTokenResponse::class, 'ajgarlag.openid_connect_provider.openid_connect.response')
            ->deprecate('ajgarlag/openid-connect-provider-bundle', '0.2.2', 'The "%alias_id%" alias is deprecated, use "ajgarlag.openid_connect_provider.openid_connect.response" instead.')

        ->set('ajgarlag.openid_connect_provider.grant.id_token', IdTokenGrant::class)
            ->args([
                service('ajgarlag.openid_connect_provider.openid_connect.response'),
                null,
            ])
        ->alias(IdTokenGrant::class, 'ajgarlag.openid_connect_provider.grant.id_token')
            ->deprecate('ajgarlag/openid-connect-provider-bundle', '0.2.2', 'The "%alias_id%" alias is deprecated, use "ajgarlag.openid_connect_provider.grant.id_token" instead.')

         ->set('ajgarlag.openid_connect_provider.listener.post_logout_redirect', PostLogoutRedirectListener::class)
            ->args([
                service(PostLogoutRedirectUriStorageInterface::class),
                service('security.helper'),
            ])
            ->tag('kernel.event_subscriber')
        ->alias(PostLogoutRedirectListener::class, 'ajgarlag.openid_connect_provider.listener.post_logout_redirect')
            ->deprecate('ajgarlag/openid-connect-provider-bundle', '0.2.2', 'The "%alias_id%" alias is deprecated, use "ajgarlag.openid_connect_provider.listener.post_logout_redirect" instead.')

        ->set('ajgarlag.openid_connect_provider.command.show_relying_party', ShowRelyingPartyCommand::class)
            ->args([
                service(ClientManagerInterface::class),
                service(RelyingPartyManagerInterface::class),
            ])
            ->tag('console.command', ['command' => 'ajgarlag:openid-connect-provider:show-relying-party'])
        ->alias(ShowRelyingPartyCommand::class, 'ajgarlag.openid_connect_provider.command.show_relying_party')
            ->deprecate('ajgarlag/openid-connect-provider-bundle', '0.2.2', 'The "%alias_id%" alias is deprecated, use "ajgarlag.openid_connect_provider.command.show_relying_party" instead.')

        ->set('ajgarlag.openid_connect_provider.command.save_relying_party', SaveRelyingPartyCommand::class)
            ->args([
                service(ClientManagerInterface::class),
                service(RelyingPartyManagerInterface::class),
            ])
            ->tag('console.command', ['command' => 'ajgarlag:openid-connect-provider:save-relying-party'])
        ->alias(SaveRelyingPartyCommand::class, 'ajgarlag.openid_connect_provider.command.save_relying_party')
            ->deprecate('ajgarlag/openid-connect-provider-bundle', '0.2.2', 'The "%alias_id%" alias is deprecated, use "ajgarlag.openid_connect_provider.command.save_relying_party" instead.')

        ->set('ajgarlag.openid_connect_provider.controller.discovery', DiscoveryController::class)
            ->args([
                service('league.oauth2_server.authorization_server'),
                service('router'),
                null,
                null,
                null,
                null,
            ])
            ->tag('controller.service_arguments')
        ->alias(DiscoveryController::class, 'ajgarlag.openid_connect_provider.controller.discovery')
            ->deprecate('ajgarlag/openid-connect-provider-bundle', '0.2.2', 'The "%alias_id%" alias is deprecated, use "ajgarlag.openid_connect_provider.controller.discovery" instead.')

        ->set('ajgarlag.openid_connect_provider.controller.end_session', EndSessionController::class)
            ->args([
                service('security.logout_url_generator'),
                service(ClientManagerInterface::class),
                service(RelyingPartyManagerInterface::class),
                null,
                service(PostLogoutRedirectUriStorageInterface::class),
                service('security.helper'),
                service('twig'),
                service('security.http_utils'),
                null,
            ])
            ->tag('controller.service_arguments')
        ->alias(EndSessionController::class, 'ajgarlag.openid_connect_provider.controller.end_session')
            ->deprecate('ajgarlag/openid-connect-provider-bundle', '0.2.2', 'The "%alias_id%" alias is deprecated, use "ajgarlag.openid_connect_provider.controller.end_session" instead.')

        ->set('ajgarlag.openid_connect_provider.controller.jwks', JwksController::class)
            ->args([
                null,
            ])
            ->tag('controller.service_arguments')
        ->alias(JwksController::class, 'ajgarlag.openid_connect_provider.controller.jwks')
            ->deprecate('ajgarlag/openid-connect-provider-bundle', '0.2.2', 'The "%alias_id%" alias is deprecated, use "ajgarlag.openid_connect_provider.controller.jwks" instead.')

        ->set('ajgarlag.openid_connect_provider.logout.post_logout_redirect_storage.cache', CachePostLogoutRedirectUriStorage::class)
            ->args([
                service('cache.app'),
                60,
            ])
        ->alias(CachePostLogoutRedirectUriStorage::class, 'ajgarlag.openid_connect_provider.logout.post_logout_redirect_storage.cache')
            ->deprecate('ajgarlag/openid-connect-provider-bundle', '0.2.2', 'The "%alias_id%" alias is deprecated, use "ajgarlag.openid_connect_provider.logout.post_logout_redirect_storage.cache" instead.')
        ->alias(PostLogoutRedirectUriStorageInterface::class, 'ajgarlag.openid_connect_provider.logout.post_logout_redirect_storage.cache')
    ;
};
