<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\DiscoveryController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller\JwksController;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OAuth2\IdTokenGrant;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect\IdTokenResponse;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Repository\IdentityProvider;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BundleInitializationTest extends KernelTestCase
{
    use KernelTestCaseTrait;

    public function testInitBundle(): void
    {
        self::bootKernel(self::getDefaultKernelOptions());
        $container = self::getContainer();

        $this->assertTrue($container->has('ajgarlag.openid_connect_provider.repository.identity_provider'));
        $service = $container->get('ajgarlag.openid_connect_provider.repository.identity_provider');
        $this->assertInstanceOf(IdentityProviderInterface::class, $service);
        $this->assertInstanceOf(IdentityProvider::class, $service);
        $this->assertTrue($container->has(IdentityProviderInterface::class));
        $this->assertTrue($container->has(IdentityProvider::class));

        $this->assertTrue($container->has('ajgarlag.openid_connect_provider.openid_connect.claim_extractor'));
        $this->assertInstanceOf(ClaimExtractor::class, $container->get('ajgarlag.openid_connect_provider.openid_connect.claim_extractor'));

        $this->assertTrue($container->has('ajgarlag.openid_connect_provider.openid_connect.response'));
        $this->assertInstanceOf(IdTokenResponse::class, $container->get('ajgarlag.openid_connect_provider.openid_connect.response'));
        $this->assertTrue($container->has(IdTokenResponse::class));

        $this->assertTrue($container->has('ajgarlag.openid_connect_provider.grant.id_token'));
        $this->assertInstanceOf(IdTokenGrant::class, $container->get('ajgarlag.openid_connect_provider.grant.id_token'));
        $this->assertTrue($container->has(IdTokenGrant::class));

        $this->assertTrue($container->has('ajgarlag.openid_connect_provider.controller.discovery'));
        $this->assertInstanceOf(DiscoveryController::class, $container->get('ajgarlag.openid_connect_provider.controller.discovery'));
        $this->assertTrue($container->has(DiscoveryController::class));

        $this->assertTrue($container->has('ajgarlag.openid_connect_provider.controller.jwks'));
        $this->assertInstanceOf(JwksController::class, $container->get('ajgarlag.openid_connect_provider.controller.jwks'));
        $this->assertTrue($container->has(JwksController::class));
    }
}
