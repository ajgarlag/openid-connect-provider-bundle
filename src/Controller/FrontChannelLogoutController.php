<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\RelyingPartyManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\HttpUtils;
use Twig\Environment;

final readonly class FrontChannelLogoutController
{
    public function __construct(
        private UriSigner $uriSigner,
        private ClientManagerInterface $clientManager,
        private RelyingPartyManagerInterface $relyingPartyManager,
        private Environment $twigEnvironment,
        private HttpUtils $httpUtils,
        private string $logoutTargetPath,
    ) {
    }

    /**
     * @param non-empty-string[] $clientIds
     */
    public function __invoke(
        Request $request,
        string $sid,
        #[MapQueryParameter('client_ids')] array $clientIds = [],
        #[MapQueryParameter('redirect_uri')] ?string $redirectUri = null,
    ): Response {
        if (!$this->uriSigner->checkRequest($request)) {
            throw new BadRequestHttpException('Invalid signed URL.');
        }

        $targetUri = $redirectUri ?? $this->httpUtils->generateUri($request, $this->logoutTargetPath);

        if (!$request->hasSession()) {
            return new RedirectResponse($targetUri);
        }

        $frontChannelLogoutUris = [];
        foreach ($clientIds as $clientId) {
            if (null === $client = $this->clientManager->find($clientId)) {
                continue;
            }

            $relyingParty = $this->relyingPartyManager->get($client);

            if (null === $frontChannelLogoutUri = $relyingParty->getFrontChannelLogoutUri()) {
                continue;
            }

            $frontChannelLogoutUri .= (str_contains($frontChannelLogoutUri, '?') ? '&' : '?') . http_build_query(['sid' => $sid, 'iss' => $request->getSchemeAndHttpHost()]);

            $frontChannelLogoutUris[$clientId] = $frontChannelLogoutUri;
        }

        if (0 === \count($frontChannelLogoutUris)) {
            return new RedirectResponse($targetUri);
        }

        return new Response($this->twigEnvironment->render('@AjgarlagOpenIDConnectProvider/front_channel_logout.html.twig', [
            'target_uri' => $targetUri,
            'front_channel_logout_uris' => $frontChannelLogoutUris,
        ]));
    }
}
