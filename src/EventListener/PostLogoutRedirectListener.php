<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\PostLogoutRedirectUriStorageInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class PostLogoutRedirectListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly PostLogoutRedirectUriStorageInterface $redirectUriStorage,
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

        $redirectUri = $this->redirectUriStorage->get($firewallConfig->getName());
        if (null === $redirectUri) {
            return;
        }

        $this->redirectUriStorage->delete($firewallConfig->getName());

        $event->setResponse(new RedirectResponse($redirectUri));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
