<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Event;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\IdTokenInterface;
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
