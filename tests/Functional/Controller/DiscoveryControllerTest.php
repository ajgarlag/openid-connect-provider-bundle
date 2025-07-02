<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DiscoveryControllerTest extends WebTestCase
{
    public function testDiscoveryResponse(): void
    {
        $client = static::createClient();

        $client->request('GET', '/.well-known/openid-configuration');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', '*');
    }
}
