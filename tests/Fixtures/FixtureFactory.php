<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Fixtures;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\RelyingPartyManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\RelyingParty;
use League\Bundle\OAuth2ServerBundle\Manager\AccessTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\AuthorizationCodeManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\RefreshTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ScopeManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\AccessToken;
use League\Bundle\OAuth2ServerBundle\Model\AuthorizationCode;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\Model\RefreshToken;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;

/**
 * Development hints:
 *
 * You can easily generate token identifiers using the following command:
 * --- dev/bin/php -r "echo bin2hex(random_bytes(40)) . PHP_EOL;"
 */
final class FixtureFactory
{
    public const FIXTURE_AUTH_CODE_OPENID_CONNECT = 'aaa70e8152259988b3c8e9e8cff604019bb986eb226bd126da189829b95a2be631e2506042064e12';

    public const FIXTURE_CLIENT_OPENID_CONNECT = 'client_openid_connect';

    public const FIXTURE_CLIENT_OPENID_CONNECT_REDIRECT_URI = 'https://example.org/openid_connect/redirect-uri';

    public const FIXTURE_CLIENT_OPENID_CONNECT_POST_LOGOUT_REDIRECT_URI = 'https://example.org/openid_connect/bye';

    public const FIXTURE_SCOPE_OPENID = 'openid';

    public const FIXTURE_USER = 'user';
    public const FIXTURE_PASSWORD = 'password';

    public static function initializeFixtures(
        ScopeManagerInterface $scopeManager,
        ClientManagerInterface $clientManager,
        RelyingPartyManagerInterface $relyingPartyManager,
        AccessTokenManagerInterface $accessTokenManager,
        RefreshTokenManagerInterface $refreshTokenManager,
        AuthorizationCodeManagerInterface $authCodeManager,
    ): void {
        foreach (self::createScopes() as $scope) {
            $scopeManager->save($scope);
        }

        foreach (self::createClients() as $client) {
            $clientManager->save($client);
            $relyingPartyManager->save(self::createRelyingParty($client));
        }

        foreach (self::createAccessTokens($scopeManager, $clientManager) as $accessToken) {
            $accessTokenManager->save($accessToken);
        }

        foreach (self::createRefreshTokens($accessTokenManager) as $refreshToken) {
            $refreshTokenManager->save($refreshToken);
        }

        foreach (self::createAuthorizationCodes($clientManager) as $authorizationCode) {
            $authCodeManager->save($authorizationCode);
        }
    }

    /**
     * @return AccessToken[]
     */
    private static function createAccessTokens(ScopeManagerInterface $scopeManager, ClientManagerInterface $clientManager): array
    {
        $accessTokens = [];

        return $accessTokens;
    }

    /**
     * @return RefreshToken[]
     */
    private static function createRefreshTokens(AccessTokenManagerInterface $accessTokenManager): array
    {
        $refreshTokens = [];

        return $refreshTokens;
    }

    /**
     * @return AuthorizationCode[]
     */
    public static function createAuthorizationCodes(ClientManagerInterface $clientManager): array
    {
        $authorizationCodes = [];

        $authorizationCodes[] = new AuthorizationCode(
            self::FIXTURE_AUTH_CODE_OPENID_CONNECT,
            new \DateTimeImmutable('+2 minute'),
            $clientManager->find(self::FIXTURE_CLIENT_OPENID_CONNECT),
            self::FIXTURE_USER,
            []
        );

        return $authorizationCodes;
    }

    /**
     * @return Client[]
     */
    private static function createClients(): array
    {
        $clients = [];

        $clients[] = (new Client('name', self::FIXTURE_CLIENT_OPENID_CONNECT, 'secret_openid_connect'))
            ->setRedirectUris(new RedirectUri(self::FIXTURE_CLIENT_OPENID_CONNECT_REDIRECT_URI))
            ->setScopes(new Scope(self::FIXTURE_SCOPE_OPENID));

        return $clients;
    }

    private static function createRelyingParty(Client $client): RelyingParty
    {
        $relyingParty = new RelyingParty($client->getIdentifier(), $client);

        if (self::FIXTURE_CLIENT_OPENID_CONNECT !== $client->getIdentifier()) {
            return $relyingParty;
        }

        return $relyingParty->setPostLogoutRedirectUris(
            new RedirectUri(self::FIXTURE_CLIENT_OPENID_CONNECT_POST_LOGOUT_REDIRECT_URI)
        );
    }

    /**
     * @return Scope[]
     */
    private static function createScopes(): array
    {
        $scopes = [];

        return $scopes;
    }
}
