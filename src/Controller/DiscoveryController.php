<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Event\ProviderMetadataResolveEvent;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OAuth2\AuthorizationServer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class DiscoveryController
{
    public function __construct(
        private AuthorizationServer $authorizationServer,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $event = new ProviderMetadataResolveEvent($request, $this->authorizationServer);
        $this->eventDispatcher->dispatch($event);

        return new JsonResponse(
            $event->getMetadata(),
            JsonResponse::HTTP_OK,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }
}
