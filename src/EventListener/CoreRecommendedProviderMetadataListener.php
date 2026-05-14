<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Event\ProviderMetadataResolveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class CoreRecommendedProviderMetadataListener implements EventSubscriberInterface
{
    /**
     * @param list<non-empty-string> $supportedScopes
     * @param list<non-empty-string> $supportedClaims
     */
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private array $supportedScopes,
        private array $supportedClaims,
        private ?string $userInfoEndpointRoute,
    ) {
    }

    public function onOpenIdProviderMetadataResolve(ProviderMetadataResolveEvent $event): void
    {
        $metadata = $event->getMetadata();

        $metadata['scope_supported'] = $this->supportedScopes;
        $metadata['claims_supported'] = $this->supportedClaims;
        if (null !== $this->userInfoEndpointRoute) {
            $metadata['userinfo_endpoint'] = $this->urlGenerator->generate($this->userInfoEndpointRoute, [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $event->setMetadata($metadata);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProviderMetadataResolveEvent::class => 'onOpenIdProviderMetadataResolve',
        ];
    }
}
