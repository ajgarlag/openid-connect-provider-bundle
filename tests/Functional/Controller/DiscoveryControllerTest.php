<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DiscoveryControllerTest extends WebTestCase
{
    public function testDiscoveryResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/.well-known/openid-configuration');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', '*');

        $responseData = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('issuer', $responseData);
        $this->assertSame('http://localhost', $responseData['issuer']);
        $this->assertArrayHasKey('authorization_endpoint', $responseData);
        $this->assertSame('http://localhost/authorize', $responseData['authorization_endpoint']);
        $this->assertArrayHasKey('token_endpoint', $responseData);
        $this->assertSame('http://localhost/token', $responseData['token_endpoint']);
        $this->assertArrayHasKey('jwks_uri', $responseData);
        $this->assertSame('http://localhost/jwks', $responseData['jwks_uri']);
        $this->assertArrayHasKey('response_types_supported', $responseData);
        $this->assertSame(['code', 'token', 'id_token', 'id_token token'], $responseData['response_types_supported']);
        $this->assertArrayHasKey('subject_types_supported', $responseData);
        $this->assertSame(['public'], $responseData['subject_types_supported']);
        $this->assertArrayHasKey('id_token_signing_alg_values_supported', $responseData);
        $this->assertSame(['RS256'], $responseData['id_token_signing_alg_values_supported']);
        $this->assertArrayHasKey('scope_supported', $responseData);
        $this->assertSame(['openid'], $responseData['scope_supported']);
        $this->assertArrayHasKey('claims_supported', $responseData);
        $this->assertSame(['iss', 'sub', 'aud', 'exp', 'iat', 'azp', 'nonce', 'sid'], $responseData['claims_supported']);
        $this->assertArrayHasKey('end_session_endpoint', $responseData);
        $this->assertSame('http://localhost/end-session', $responseData['end_session_endpoint']);
    }
}
