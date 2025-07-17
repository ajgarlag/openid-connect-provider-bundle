<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Functional\Controller;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\WebTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DiscoveryControllerTest extends WebTestCase
{
    use WebTestCaseTrait;

    public function testDiscoveryResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/.well-known/openid-configuration');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', '*');
    }
}
