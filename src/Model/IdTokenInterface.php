<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Model;

interface IdTokenInterface
{
    /**
     * @return non-empty-string
     */
    public function getIssuer(): string;

    /**
     * @return non-empty-string
     */
    public function getSubject(): string;

    /**
     * @return non-empty-string[]
     */
    public function getAudience(): array;

    public function getExpirationTime(): \DateTimeImmutable;

    public function getIssuedAtTime(): \DateTimeImmutable;

    public function getAuthenticatedAtTime(): ?\DateTimeImmutable;

    /**
     * @param non-empty-string $name
     */
    public function getClaim(string $name, mixed $default = null): mixed;
}
