<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout;

use Psr\Cache\CacheItemPoolInterface;

final readonly class CachePostLogoutRedirectUriStorage implements PostLogoutRedirectUriStorageInterface
{
    private const CACHE_KEY_PREFIX = 'ajgarlag.openid-connect-provider.logout.';

    public function __construct(
        private CacheItemPoolInterface $cache,
        private int $ttl,
    ) {
    }

    public function save(string $firewallName, string $uri): void
    {
        $item = $this->cache->getItem(self::CACHE_KEY_PREFIX . $firewallName);
        $item->set($uri);
        $item->expiresAfter($this->ttl);
        $this->cache->save($item);
    }

    public function get(string $firewallName): ?string
    {
        $item = $this->cache->getItem(self::CACHE_KEY_PREFIX . $firewallName);

        return $item->isHit() ? $item->get() : null;
    }

    public function delete(string $firewallName): void
    {
        $this->cache->deleteItem(self::CACHE_KEY_PREFIX . $firewallName);
    }
}
