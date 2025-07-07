<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class JwksControllerTest extends WebTestCase
{
    public function testJwksResponse(): void
    {
        $client = static::createClient();

        $client->request('GET', '/jwks');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', '*');
    }
}
