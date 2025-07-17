<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class PostLogoutRedirectListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }

        $item = $this->cache->getItem('ajgarlag.openid-connect-provider.logout.' . $request->attributes->get('_firewall_context'));
        if (!$item->isHit()) {
            return;
        }

        $this->cache->deleteItem('ajgarlag.openid-connect-provider.logout.' . $request->attributes->get('_firewall_context'));

        $event->setResponse(new RedirectResponse($item->get()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
