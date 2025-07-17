<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout;

interface LoggedInRelyingPartyStorageInterface
{
    /**
     * @param non-empty-string $clientId
     */
    public function add(string $sid, string $clientId): void;

    /**
     * @return non-empty-string[]
     */
    public function get(string $sid): array;

    public function delete(string $sid): void;
}
