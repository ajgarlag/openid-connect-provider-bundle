<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\LoggedInRelyingPartyStorageInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\PostLogoutRedirectUriStorageInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect\SessionSidManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\HttpUtils;

final readonly class FrontChannelLogoutRedirectListener implements EventSubscriberInterface
{
    public function __construct(
        private PostLogoutRedirectUriStorageInterface $redirectUriStorage,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
        private UriSigner $uriSigner,
        private SessionSidManager $sessionSidManager,
        private HttpUtils $httpUtils,
        private LoggedInRelyingPartyStorageInterface $loggedInRelyingPartyStorage,
        private string $frontChannelLogoutRouteName,
        private string $logoutTargetPath,
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

        if (null === $sid = $this->sessionSidManager->getSid($firewallConfig)) {
            return;
        }

        if ([] === $clientIds = $this->loggedInRelyingPartyStorage->get($sid)) {
            return;
        }

        $this->loggedInRelyingPartyStorage->delete($sid);

        $redirectUri = $this->redirectUriStorage->get($firewallConfig->getName()) ?? $this->httpUtils->generateUri($request, $this->logoutTargetPath);

        $frontChannelLogoutUrl = $this->urlGenerator->generate(
            $this->frontChannelLogoutRouteName,
            [
                'sid' => $sid,
                'client_ids' => $clientIds,
                'redirect_uri' => $redirectUri,
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
