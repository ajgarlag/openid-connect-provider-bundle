<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Acceptance;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Fixtures\FixtureFactory;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\TestHelper;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use League\Bundle\OAuth2ServerBundle\Manager\AuthorizationCodeManagerInterface;
use League\Bundle\OAuth2ServerBundle\Tests\Acceptance\TokenEndpointTest as LeagueTokenEndpointTest;

final class TokenEndpointTest extends LeagueTokenEndpointTest
{
    use AcceptanceTestTrait;

    /**
     * @group time-sensitive
     */
    public function testSuccessfulIdTokenRequest(): void
    {
        $authCodeOpenID = $this->client
            ->getContainer()
            ->get(AuthorizationCodeManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_AUTH_CODE_OPENID_CONNECT);

        $this->client->request('POST', '/token', [
            'client_id' => 'client_openid_connect',
            'client_secret' => 'secret_openid_connect',
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'https://example.org/openid_connect/redirect-uri',
            'code' => TestHelper::generateEncryptedAuthCodePayload($authCodeOpenID, 'n0nc3'),
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));

        $jsonResponse = json_decode($response->getContent(), true);

        $this->assertSame('Bearer', $jsonResponse['token_type']);
        $this->assertEqualsWithDelta(3600, $jsonResponse['expires_in'], 1.0);
        $this->assertNotEmpty($jsonResponse['access_token']);

        $this->assertNotEmpty($jsonResponse['id_token']);
        $token = (new Parser(new JoseEncoder()))->parse($jsonResponse['id_token']);

        $this->assertInstanceOf(Plain::class, $token);
        $this->assertSame('http://localhost', $token->claims()->get('iss'));
        $this->assertNotEmpty($token->claims()->get('sub'));
        $this->assertNotEmpty($token->claims()->get('aud'));
        $this->assertNotEmpty($token->claims()->get('exp'));
        $this->assertNotEmpty($token->claims()->get('iat'));
    }
}
