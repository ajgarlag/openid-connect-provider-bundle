<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Tests\Acceptance;

use Ajgarlag\Bundle\OidcProviderBundle\Tests\Fixtures\FixtureFactory;
use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use League\Bundle\OAuth2ServerBundle\Tests\Acceptance\AuthorizationEndpointTest as LeagueAuthorizationEndpointTest;

final class AuthorizationEndpointTest extends LeagueAuthorizationEndpointTest
{
    use AcceptanceTestTrait;

    public function testSuccessfulCodeRequest(): void
    {
        $this->assertTrue(true);
        $this->client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE, static function (AuthorizationRequestResolveEvent $event): void {
                $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
            });

        $this->loginUser();

        $this->client->request(
            'GET',
            '/authorize',
            [
                'client_id' => FixtureFactory::FIXTURE_CLIENT_OIDC,
                'response_type' => 'code',
                'state' => 'foobar',
                'scope' => 'openid',
            ]
        );

        $response = $this->client->getResponse();

        $this->assertSame(302, $response->getStatusCode());
        $redirectUri = $response->headers->get('Location');

        $this->assertStringStartsWith(FixtureFactory::FIXTURE_CLIENT_OIDC_REDIRECT_URI, $redirectUri);
        $query = [];
        parse_str(parse_url((string) $redirectUri, \PHP_URL_QUERY), $query);
        $this->assertArrayHasKey('code', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertEquals('foobar', $query['state']);
    }
}
