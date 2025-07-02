<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Tests\Acceptance;

use Ajgarlag\Bundle\OidcProviderBundle\Tests\Fixtures\FixtureFactory;
use League\Bundle\OAuth2ServerBundle\Manager\AccessTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\AuthorizationCodeManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\RefreshTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ScopeManagerInterface;

trait AcceptanceTestTrait
{
    private function loginUser(string $username = FixtureFactory::FIXTURE_USER, string $firewallContext = 'authorization'): void
    {
        $userProvider = static::getContainer()->get('security.user_providers');
        $user = $userProvider->loadUserByIdentifier($username);
        $this->client->loginUser($user, $firewallContext);
    }

    protected function setUp(): void
    {
        parent::setUp();

        FixtureFactory::initializeFixtures(
            $this->client->getContainer()->get(ScopeManagerInterface::class),
            $this->client->getContainer()->get(ClientManagerInterface::class),
            $this->client->getContainer()->get(AccessTokenManagerInterface::class),
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class),
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)
        );
    }
}
