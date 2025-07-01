<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Tests\Fixtures;

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
    public const FIXTURE_AUTH_CODE_OIDC = 'aaa70e8152259988b3c8e9e8cff604019bb986eb226bd126da189829b95a2be631e2506042064e12';

    public const FIXTURE_CLIENT_OIDC = 'client_oidc';

    public const FIXTURE_CLIENT_OIDC_REDIRECT_URI = 'https://example.org/oidc/redirect-uri';

    public const FIXTURE_SCOPE_OPENID = 'openid';

    public const FIXTURE_USER = 'user';
    public const FIXTURE_PASSWORD = 'password';

    public static function createUser(array $roles = []): User
    {
        $user = new User();
        $user['roles'] = $roles;

        return $user;
    }

    public static function initializeFixtures(
        ScopeManagerInterface $scopeManager,
        ClientManagerInterface $clientManager,
        AccessTokenManagerInterface $accessTokenManager,
        RefreshTokenManagerInterface $refreshTokenManager,
        AuthorizationCodeManagerInterface $authCodeManager,
    ): void {
        foreach (self::createScopes() as $scope) {
            $scopeManager->save($scope);
        }

        foreach (self::createClients() as $client) {
            $clientManager->save($client);
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
            self::FIXTURE_AUTH_CODE_OIDC,
            new \DateTimeImmutable('+2 minute'),
            $clientManager->find(self::FIXTURE_CLIENT_OIDC),
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

        $clients[] = (new Client('name', self::FIXTURE_CLIENT_OIDC, 'secret_oidc'))
            ->setRedirectUris(new RedirectUri(self::FIXTURE_CLIENT_OIDC_REDIRECT_URI))
            ->setScopes(new Scope(self::FIXTURE_SCOPE_OPENID));

        return $clients;
    }

    /**
     * @return Scope[]
     */
    private static function createScopes(): array
    {
        $scopes = [];

        $scopes[] = new Scope(self::FIXTURE_SCOPE_OPENID);

        return $scopes;
    }
}
