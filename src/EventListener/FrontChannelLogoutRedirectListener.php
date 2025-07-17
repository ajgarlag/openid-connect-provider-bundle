<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\LoggedInRelyingPartyStorageInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\PostLogoutRedirectUriStorageInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect\SessionSidTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\HttpUtils;

final class FrontChannelLogoutRedirectListener implements EventSubscriberInterface
{
    use SessionSidTrait;

    public function __construct(
        private readonly PostLogoutRedirectUriStorageInterface $redirectUriStorage,
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UriSigner $uriSigner,
        private readonly HttpUtils $httpUtils,
        private readonly LoggedInRelyingPartyStorageInterface $loggedInRelyingPartyStorage,
        private readonly string $frontChannelLogoutRouteName,
        private readonly string $logoutTargetPath,
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

        if (null === $sid = $this->getSid($request->getSession())) {
            return;
        }

        if ([] === $clientIds = $this->loggedInRelyingPartyStorage->get($sid)) {
            return;
        }

        $redirectUri = $this->redirectUriStorage->get($firewallConfig->getName()) ?? $this->httpUtils->generateUri($request, $this->logoutTargetPath);

        $frontChannelLogoutUrl = $this->urlGenerator->generate(
            $this->frontChannelLogoutRouteName,
            [
                'redirect_uri' => $redirectUri,
                'client_id' => $clientIds,
                'sid' => $sid,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $signedFrontChannelLogoutUrl = $this->uriSigner->sign($frontChannelLogoutUrl, time() + 60);

        $this->redirectUriStorage->save($firewallConfig->getName(), $signedFrontChannelLogoutUrl);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => ['onLogout', 10],
        ];
    }
}
