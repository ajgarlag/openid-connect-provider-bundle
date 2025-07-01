<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Tests;

use League\Bundle\OAuth2ServerBundle\Tests\TestKernel as LeagueTestKernel;

final class TestKernel extends LeagueTestKernel
{
    public function registerBundles(): iterable
    {
        return [
            ...parent::registerBundles(),
            new \Ajgarlag\Bundle\OidcProviderBundle\AjgarlagOidcProviderBundle(),
        ];
    }
}
