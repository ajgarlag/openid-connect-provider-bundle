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
        $redirectLocation = $client->getResponse()->headers->get('Location');
        $this->assertSame('http://localhost/front-channel-logout', parse_url($redirectLocation, \PHP_URL_SCHEME) . '://' . parse_url($redirectLocation, \PHP_URL_HOST) . parse_url($redirectLocation, \PHP_URL_PATH));
        parse_str(parse_url($redirectLocation, \PHP_URL_QUERY), $queryParams);
        $this->assertSame('https://example.org/openid_connect/bye', $queryParams['redirect_uri'] ?? '');
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
        $redirectLocation = $client->getResponse()->headers->get('Location');
        $this->assertSame('http://localhost/front-channel-logout', parse_url($redirectLocation, \PHP_URL_SCHEME) . '://' . parse_url($redirectLocation, \PHP_URL_HOST) . parse_url($redirectLocation, \PHP_URL_PATH));
        parse_str(parse_url($redirectLocation, \PHP_URL_QUERY), $queryParams);
        $this->assertSame('http://localhost/', $queryParams['redirect_uri'] ?? '');
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
