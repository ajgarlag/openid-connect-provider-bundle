<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class ClaimsResolveEvent extends Event
{
    /**
     * @var array<non-empty-string, mixed>
     */
    private $claims = [];

    public function __construct(
        private readonly string $identifier,
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function getClaims(): array
    {
        return $this->claims;
    }

    /**
     * @param array<non-empty-string, mixed> $claims
     */
    public function setClaims(array $claims): self
    {
        $this->claims = $claims;

        return $this;
    }
}
