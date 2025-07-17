<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Functional\Controller;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\WebTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class JwksControllerTest extends WebTestCase
{
    use WebTestCaseTrait;

    public function testJwksResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/jwks');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', '*');
    }
}
