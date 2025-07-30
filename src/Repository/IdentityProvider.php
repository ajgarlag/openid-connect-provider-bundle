<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Repository;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Event\UserClaimsResolveEvent;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\Identity;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class IdentityProvider implements IdentityProviderInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param non-empty-string $identifier
     *
     * @return Identity
     */
    public function getUserEntityByIdentifier($identifier)
    {
        $user = new Identity();
        $user->setIdentifier($identifier);

        /** @var UserClaimsResolveEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new UserClaimsResolveEvent($identifier)
        );

        $user->setClaims($event->getClaims());

        return $user;
    }
}
