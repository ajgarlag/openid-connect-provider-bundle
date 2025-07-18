<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Acceptance;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientExtensionManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Fixtures\FixtureFactory;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\WebTestCaseTrait;
use League\Bundle\OAuth2ServerBundle\Manager\AccessTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\AuthorizationCodeManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\RefreshTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ScopeManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractAcceptanceTestCase extends WebTestCase
{
    use WebTestCaseTrait;

    protected static function bootKernel(array $options = []): KernelInterface
    {
        $kernel = parent::bootKernel($options);

        FixtureFactory::initializeFixtures(
            self::getContainer()->get(ScopeManagerInterface::class),
            self::getContainer()->get(ClientManagerInterface::class),
            self::getContainer()->get(ClientExtensionManagerInterface::class),
            self::getContainer()->get(AccessTokenManagerInterface::class),
            self::getContainer()->get(RefreshTokenManagerInterface::class),
            self::getContainer()->get(AuthorizationCodeManagerInterface::class)
        );

        return $kernel;
    }

    protected function getUser(string $userIdentifier = 'user'): UserInterface
    {
        $userProvider = static::getContainer()->get('security.user_providers');

        return $userProvider->loadUserByIdentifier($userIdentifier);
    }
}
