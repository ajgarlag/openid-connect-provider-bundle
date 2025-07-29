<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout;

interface PostLogoutRedirectUriStorageInterface
{
    public function save(string $firewallName, string $uri): void;

    public function get(string $firewallName): ?string;

    public function delete(string $firewallName): void;
}
