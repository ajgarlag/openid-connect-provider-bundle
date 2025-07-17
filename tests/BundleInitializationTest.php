<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Repository\IdentityProvider;
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
    }
}
