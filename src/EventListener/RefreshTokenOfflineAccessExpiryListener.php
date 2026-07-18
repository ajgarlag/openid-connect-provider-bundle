<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener;

use League\OAuth2\Server\RequestRefreshTokenEvent;
use Psr\Clock\ClockInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class RefreshTokenOfflineAccessExpiryListener implements EventSubscriberInterface
{
    public function __construct(
        private bool $requireOfflineAccessScope = false,
        private ?ClockInterface $clock = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'refresh_token.issued' => 'onRefreshTokenIssued',
        ];
    }

    public function onRefreshTokenIssued(RequestRefreshTokenEvent $event): void
    {
        if (!$this->requireOfflineAccessScope) {
            return;
        }

        $refreshToken = $event->getRefreshToken();

        foreach ($refreshToken->getAccessToken()->getScopes() as $scope) {
            if ('offline_access' === $scope->getIdentifier()) {
                return;
            }
        }

        $refreshToken->setExpiryDateTime($this->clock?->now() ?? new \DateTimeImmutable());
    }
}
