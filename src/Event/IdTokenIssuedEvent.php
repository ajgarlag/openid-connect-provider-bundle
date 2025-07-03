<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Event;

use Ajgarlag\Bundle\OidcProviderBundle\Model\IdTokenInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class IdTokenIssuedEvent extends Event
{
    public function __construct(
        private readonly IdTokenInterface $idToken,
    ) {
    }

    public function getIdToken(): IdTokenInterface
    {
        return $this->idToken;
    }
}
