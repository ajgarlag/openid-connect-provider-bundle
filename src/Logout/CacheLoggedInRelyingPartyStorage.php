<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout;

use Psr\Cache\CacheItemPoolInterface;

final readonly class CacheLoggedInRelyingPartyStorage implements LoggedInRelyingPartyStorageInterface
{
    private const CACHE_KEY_PREFIX = 'ajgarlag.openid-connect-provider.logged_in_relaying_party.';

    public function __construct(
        private CacheItemPoolInterface $cache,
        private int $ttl,
    ) {
    }

    public function add(string $sid, string $clientId): void
    {
        $item = $this->cache->getItem(self::CACHE_KEY_PREFIX . $sid);
        $value = $item->isHit() ? $item->get() : [];
        $value[$clientId] = $clientId;
        $item->set($value);
        $item->expiresAfter($this->ttl);
        $this->cache->save($item);
    }

    public function get(string $sid): array
    {
        $item = $this->cache->getItem(self::CACHE_KEY_PREFIX . $sid);

        return $item->isHit() ? array_values($item->get()) : [];
    }

    public function delete(string $sid): void
    {
        $this->cache->deleteItem(self::CACHE_KEY_PREFIX . $sid);
    }
}
