<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Tests\Acceptance;

use Ajgarlag\Bundle\OidcProviderBundle\Tests\Fixtures\FixtureFactory;
use Ajgarlag\Bundle\OidcProviderBundle\Tests\TestHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use League\Bundle\OAuth2ServerBundle\Manager\AccessTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\AuthorizationCodeManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\RefreshTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ScopeManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractAcceptanceTestCase extends WebTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var KernelBrowser
     */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->application = new Application($this->client->getKernel());

        TestHelper::initializeDoctrineSchema($this->application);

        /** @var Connection $connection */
        $connection = $this->client->getContainer()->get('database_connection');
        if ($connection->getDriver() instanceof AbstractSQLiteDriver) {
            // https://www.sqlite.org/foreignkeys.html
            $connection->executeQuery('PRAGMA foreign_keys = ON');
        }

        FixtureFactory::initializeFixtures(
            $this->client->getContainer()->get(ScopeManagerInterface::class),
            $this->client->getContainer()->get(ClientManagerInterface::class),
            $this->client->getContainer()->get(AccessTokenManagerInterface::class),
            $this->client->getContainer()->get(RefreshTokenManagerInterface::class),
            $this->client->getContainer()->get(AuthorizationCodeManagerInterface::class)
        );
    }
}
