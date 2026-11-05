<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Event\ProviderMetadataResolveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class RpInitiatedLogoutProviderMetadataListener implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private string $endSessionEndpointRoute,
    ) {
    }

    public function onOpenIdProviderMetadataResolve(ProviderMetadataResolveEvent $event): void
    {
        $metadata = $event->getMetadata();

        $metadata['end_session_endpoint'] = $this->urlGenerator->generate($this->endSessionEndpointRoute, [], UrlGeneratorInterface::ABSOLUTE_URL);

        $event->setMetadata($metadata);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProviderMetadataResolveEvent::class => 'onOpenIdProviderMetadataResolve',
        ];
    }
}
