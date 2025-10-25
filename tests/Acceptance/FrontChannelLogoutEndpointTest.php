<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Acceptance;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\LoggedInRelyingPartyStorageInterface;

final class FrontChannelLogoutEndpointTest extends AbstractAcceptanceTestCase
{
    public function testFrontChannelLogoutWithInvalidSignature(): void
    {
        $client = self::createClient();

        $client->loginUser($this->getUser());

        // Try to access with an invalid signature parameter that doesn't match
        $client->request('GET', '/front-channel-logout/00000000000000000000000000000000?_hash=definitely_not_a_valid_hash_for_this_request');

        // Should return 400 Bad Request when signature is invalid
        $this->assertResponseStatusCodeSame(400);
    }

    public function testFrontChannelLogoutWithoutUser(): void
    {
        $client = self::createClient();
        $signedUrl = self::getContainer()->get('uri_signer')->sign('http://localhost/front-channel-logout/00000000000000000000000000000000');

        // Access without logging in - should result in redirect since there's no session
        $client->request('GET', $signedUrl);

        // Should redirect to logout URL when no session exists
        $this->assertResponseRedirects('http://localhost/');
    }

    public function testFrontChannelLogoutRendersTemplateWhenNotTracked(): void
    {
        $client = self::createClient();

        $client->loginUser($this->getUser());

        $client->request(
            'GET',
            '/logout'
        );

        $this->assertResponseRedirects('http://localhost/');
    }

    public function testFrontChannelLogoutRendersTemplateWhenTracked(): void
    {
        $client = self::createClient();
        $client->disableReboot();

        $sid = '0123456789abcdef0123456789abcdef';
        $client->loginUser($this->getUser());

        if (method_exists($client, 'getSession')) {
            $session = $client->getSession();
        } else {
            // Symfony <=7.3
            $session = self::getContainer()->get('session.factory')->createSession();
            $session->setId($client->getCookieJar()->get($session->getName())->getValue());
        }
        $session->set('ajgarlag.openid_connect_provider.sid.main', $sid);
        $session->save();

        /** @var LoggedInRelyingPartyStorageInterface $storage */
        $storage = self::getContainer()->get(LoggedInRelyingPartyStorageInterface::class);
        $storage->add($sid, 'client_openid_connect');

        $client->request(
            'GET',
            '/logout'
        );

        // After the logout redirect, we should be redirected to front-channel-logout
        $redirectLocation = $client->getResponse()->headers->get('Location', '');

        // Verify it's redirecting to front-channel-logout
        $this->assertStringContainsString('http://localhost/front-channel-logout/', $redirectLocation);

        $client->request('GET', $redirectLocation);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('iframe[src^="https://example.org/openid_connect/front-channel-logout"]');
    }
}
