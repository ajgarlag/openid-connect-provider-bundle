<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Event\ProviderMetadataResolveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class CoreRequiredProviderMetadataListener implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private string $authorizationEndpointRoute,
        private string $tokenEndpointRoute,
        private string $jwksEndpointRoute,
    ) {
    }

    public function onOpenIdProviderMetadataResolve(ProviderMetadataResolveEvent $event): void
    {
        $request = $event->getRequest();
        $authorizationServer = $event->getAuthorizationServer();
        $metadata = $event->getMetadata();

        $metadata['issuer'] = $request->getSchemeAndHttpHost() . $request->getBasePath();
        $metadata['authorization_endpoint'] = $this->urlGenerator->generate($this->authorizationEndpointRoute, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $metadata['token_endpoint'] = $this->urlGenerator->generate($this->tokenEndpointRoute, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $metadata['jwks_uri'] = $this->urlGenerator->generate($this->jwksEndpointRoute, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $metadata['response_types_supported'] = $authorizationServer->getResponseTypesSupported();
        $metadata['subject_types_supported'] = ['public'];
        $metadata['id_token_signing_alg_values_supported'] = ['RS256'];

        $event->setMetadata($metadata);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProviderMetadataResolveEvent::class => 'onOpenIdProviderMetadataResolve',
        ];
    }
}
