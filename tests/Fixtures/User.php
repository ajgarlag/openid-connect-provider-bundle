<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Tests\Fixtures;

use Symfony\Component\Security\Core\User\UserInterface;

final class User extends \ArrayObject implements UserInterface
{
    public function getRoles(): array
    {
        return $this['roles'] ?? [];
    }

    public function getPassword(): string
    {
        return FixtureFactory::FIXTURE_PASSWORD;
    }

    public function getUserIdentifier(): string
    {
        return FixtureFactory::FIXTURE_USER;
    }

    public function eraseCredentials(): void
    {
        return;
    }
}
