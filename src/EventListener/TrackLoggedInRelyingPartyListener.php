<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Event\IdTokenIssuedEvent;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\LoggedInRelyingPartyStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class TrackLoggedInRelyingPartyListener implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private LoggedInRelyingPartyStorageInterface $loggedInRelayingPartyStorage,
    ) {
    }

    public function onTokenIssued(IdTokenIssuedEvent $event): void
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return;
        }

        if (!$request->hasSession()) {
            return;
        }

        if (null === $sid = $event->getIdToken()->getClaim('sid')) {
            return;
        }

        if (null === $relayingParty = $event->getIdToken()->getAuthorizedParty()) {
            return;
        }

        $this->loggedInRelayingPartyStorage->add($sid, $relayingParty);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            IdTokenIssuedEvent::class => 'onTokenIssued',
        ];
    }
}
