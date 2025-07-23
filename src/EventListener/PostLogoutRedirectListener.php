<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class PostLogoutRedirectListener implements EventSubscriberInterface
{
    public const CACHE_KEY_PREFIX = 'ajgarlag.openid-connect-provider.logout.';

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly Security $security,
    ) {
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }

        if (null === $firewallConfig = $this->security->getFirewallConfig($request)) {
            return;
        }

        $item = $this->cache->getItem(self::CACHE_KEY_PREFIX . $firewallConfig->getName());
        if (!$item->isHit()) {
            return;
        }

        $this->cache->deleteItem(self::CACHE_KEY_PREFIX . $firewallConfig->getName());

        $event->setResponse(new RedirectResponse($item->get()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
