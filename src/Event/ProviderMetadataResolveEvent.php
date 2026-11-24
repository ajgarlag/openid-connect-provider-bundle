<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Event;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OAuth2\AuthorizationServer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

final class ProviderMetadataResolveEvent extends Event
{
    /**
     * @var array<non-empty-string, mixed>
     */
    private array $metadata = [];

    public function __construct(
        private readonly Request $request,
        private readonly AuthorizationServer $authorizationServer,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getAuthorizationServer(): AuthorizationServer
    {
        return $this->authorizationServer;
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<non-empty-string, mixed> $metadata
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}
