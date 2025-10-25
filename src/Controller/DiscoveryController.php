<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OAuth2\AuthorizationServer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class DiscoveryController
{
    public function __construct(
        private AuthorizationServer $authorizationServer,
        private UrlGeneratorInterface $urlGenerator,
        private string $authorizationEndpointRoute,
        private string $tokenEndpointRoute,
        private string $jwksEndpointRoute,
        private string $endSessionEndpointRoute,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse(
            [
                'issuer' => $request->getSchemeAndHttpHost() . $request->getBasePath(),
                'authorization_endpoint' => $this->urlGenerator->generate($this->authorizationEndpointRoute, [], UrlGeneratorInterface::ABSOLUTE_URL),
                'token_endpoint' => $this->urlGenerator->generate($this->tokenEndpointRoute, [], UrlGeneratorInterface::ABSOLUTE_URL),
                'jwks_uri' => $this->urlGenerator->generate($this->jwksEndpointRoute, [], UrlGeneratorInterface::ABSOLUTE_URL),
                'end_session_endpoint' => $this->urlGenerator->generate($this->endSessionEndpointRoute, [], UrlGeneratorInterface::ABSOLUTE_URL),
                'response_types_supported' => $this->authorizationServer->getResponseTypesSupported(),
                'subject_types_supported' => ['public'],
                'id_token_signing_alg_values_supported' => ['RS256'],
                'frontchannel_logout_supported' => true,
                'frontchannel_logout_session_supported' => true,
            ],
            JsonResponse::HTTP_OK,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }
}
