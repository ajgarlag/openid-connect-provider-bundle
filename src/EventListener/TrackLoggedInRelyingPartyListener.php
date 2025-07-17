<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Event\IdTokenIssuedEvent;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\LoggedInRelyingPartyStorageInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect\SessionSidTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class TrackLoggedInRelyingPartyListener implements EventSubscriberInterface
{
    use SessionSidTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LoggedInRelyingPartyStorageInterface $loggedInRelayingPartyStorage,
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

        if (null === $relayingParty = $event->getIdToken()->getAuthorizedParty()) {
            return;
        }

        $sid = $this->getOrGenerateSid($request->getSession());

        $this->loggedInRelayingPartyStorage->add($sid, $relayingParty);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            IdTokenIssuedEvent::class => 'onTokenIssued',
        ];
    }
}
