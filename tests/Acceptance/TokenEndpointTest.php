<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Tests\Acceptance;

use Ajgarlag\Bundle\OidcProviderBundle\Tests\Fixtures\FixtureFactory;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use League\Bundle\OAuth2ServerBundle\Manager\AuthorizationCodeManagerInterface;
use League\Bundle\OAuth2ServerBundle\Tests\Acceptance\TokenEndpointTest as LeagueTokenEndpointTest;
use League\Bundle\OAuth2ServerBundle\Tests\TestHelper;

final class TokenEndpointTest extends LeagueTokenEndpointTest
{
    use AcceptanceTestTrait;

    /**
     * @group time-sensitive
     */
    public function testSuccessfulIdTokenRequest(): void
    {
        $authCodeOidc = $this->client
            ->getContainer()
            ->get(AuthorizationCodeManagerInterface::class)
            ->find(FixtureFactory::FIXTURE_AUTH_CODE_OIDC);

        $this->client->request('POST', '/token', [
            'client_id' => 'client_oidc',
            'client_secret' => 'secret_oidc',
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'https://example.org/oidc/redirect-uri',
            'code' => TestHelper::generateEncryptedAuthCodePayload($authCodeOidc),
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
