<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait WebTestCaseTrait
{
    use KernelTestCaseTrait;

    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        $options = array_replace(self::getDefaultKernelOptions(), $options);

        return parent::createClient($options, $server);
    }
}
