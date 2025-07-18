<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Acceptance;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Fixtures\FixtureFactory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class EndSessionEndpointTest extends AbstractAcceptanceTestCase
{
    public function testSuccessfulLogoutRequest(): void
    {
        $client = self::createClient();
        $client->getKernel()->setClearCacheAfterShutdown(false);

        $client->loginUser($this->getUser());

        $client->request(
            'GET',
            '/end-session',
            [
                'client_id' => FixtureFactory::FIXTURE_CLIENT_OPENID_CONNECT,
                'post_logout_redirect_uri' => FixtureFactory::FIXTURE_CLIENT_OPENID_CONNECT_POST_LOGOUT_REDIRECT_URI,
            ]
        );

        $this->assertInstanceOf(TokenInterface::class, $client->getContainer()->get(TokenStorageInterface::class)->getToken());
        $this->assertResponseRedirects('/logout');
        $client->followRedirect();

        $this->assertNull($client->getContainer()->get(TokenStorageInterface::class)->getToken());
        $this->assertResponseRedirects('https://example.org/openid_connect/bye');
    }

    public function testLogoutConfirmationRequest(): void
    {
        $client = self::createClient();
        $client->getKernel()->setClearCacheAfterShutdown(false);

        $client->loginUser($this->getUser());

        $crawler = $client->request(
            'GET',
            '/end-session'
        );

        $link = $crawler->selectLink('Yes')->link();
        $this->assertInstanceOf(UserInterface::class, $client->getContainer()->get(Security::class)->getUser());

        $client->click($link);

        $this->assertNull($client->getContainer()->get(Security::class)->getUser());
        $this->assertResponseRedirects('http://localhost/');
    }

    public function testLogoutCancelationRequest(): void
    {
        $client = self::createClient();
        $client->getKernel()->setClearCacheAfterShutdown(false);

        $client->loginUser($this->getUser());

        $crawler = $client->request(
            'GET',
            '/end-session'
        );

        $link = $crawler->selectLink('No')->link();
        $this->assertInstanceOf(UserInterface::class, $client->getContainer()->get(Security::class)->getUser());

        $client->click($link);

        $this->assertResponseIsSuccessful();
        $this->assertInstanceOf(UserInterface::class, $client->getContainer()->get(Security::class)->getUser());
    }
}
