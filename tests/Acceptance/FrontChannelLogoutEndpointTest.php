<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Acceptance;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Fixtures\FixtureFactory;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\TestHelper;
use League\Bundle\OAuth2ServerBundle\Manager\AuthorizationCodeManagerInterface;

final class FrontChannelLogoutEndpointTest extends AbstractAcceptanceTestCase
{
    /**
     * Tests that accessing with an invalid signature triggers a BadRequestHttpException (400).
     * The controller validates the request signature using UriSigner.
     */
    public function testFrontChannelLogoutWithInvalidSignature(): void
    {
        $client = self::createClient();

        $client->loginUser($this->getUser());

        // Try to access with an invalid signature parameter that doesn't match
        $client->request('GET', '/front-channel-logout', [
            '_hash' => 'definitely_not_a_valid_hash_for_this_request',
        ]);

        // Should return 400 Bad Request when signature is invalid
        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * Tests that accessing the front-channel-logout endpoint without a session
     * redirects to the logout URL.
     */
    public function testFrontChannelLogoutWithoutUser(): void
    {
        $client = self::createClient();
        $signedUrl = self::getContainer()->get('uri_signer')->sign('http://localhost/front-channel-logout');

        // Access without logging in - should result in redirect since there's no session
        $client->request('GET', $signedUrl);

        // Should redirect to logout URL when no session exists
        $this->assertResponseRedirects('http://localhost/');
    }

    public function xtestFrontChannelLogoutRendersTemplateWhenNotTracked(): void
    {
        $client = self::createClient();

        $client->loginUser($this->getUser());

        $client->request(
            'GET',
            '/logout'
        );

        // After the logout redirect, we should be redirected to front-channel-logout
        $redirectLocation = $client->getResponse()->headers->get('Location', '');

        // Verify it's redirecting to front-channel-logout
        $this->assertStringContainsString('http://localhost/front-channel-logout?', $redirectLocation);

        $client->request('GET', $redirectLocation);
        $this->assertResponseRedirects('http://localhost/');
    }

    /**
     * Tests that the controller filters out relying parties that don't have
     * a front-channel logout URI configured, and redirects if no valid logout URIs remain.
     */
    public function testFrontChannelLogoutRendersTemplateWhenTracked(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $client->getKernel()->setClearCacheAfterShutdown(false);

        $client->loginUser($this->getUser());

        $authCodeOpenID = $client
            ->getContainer()
            ->get(AuthorizationCodeManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_AUTH_CODE_OPENID_CONNECT)
        ;

        $client->request('POST', '/token', [
            'client_id' => 'client_openid_connect',
            'client_secret' => 'secret_openid_connect',
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'https://example.org/openid_connect/redirect-uri',
            'code' => TestHelper::generateEncryptedAuthCodePayload($authCodeOpenID, 'n0nc3'),
        ]);
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());

        $client->request(
            'GET',
            '/logout'
        );

        // After the logout redirect, we should be redirected to front-channel-logout
        $redirectLocation = $client->getResponse()->headers->get('Location', '');

        // Verify it's redirecting to front-channel-logout
        $this->assertStringContainsString('http://localhost/front-channel-logout?', $redirectLocation);

        $client->request('GET', $redirectLocation);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('iframe[src^="https://example.org/openid_connect/front-channel-logout"]');
    }
}
