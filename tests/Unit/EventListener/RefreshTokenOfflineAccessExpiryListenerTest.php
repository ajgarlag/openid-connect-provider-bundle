<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests\Unit\EventListener;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\EventListener\RefreshTokenOfflineAccessExpiryListener;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\RequestRefreshTokenEvent;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RefreshTokenOfflineAccessExpiryListenerTest extends TestCase
{
    public function testListenerExpiresRefreshTokenImmediatelyWhenOfflineAccessNotRequested(): void
    {
        $now = new \DateTimeImmutable();
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('now')->willReturn($now);

        $listener = new RefreshTokenOfflineAccessExpiryListener(true, $clock);

        // Create access token with openid scope but no offline_access
        $openidScope = $this->createMock(ScopeEntityInterface::class);
        $openidScope->method('getIdentifier')->willReturn('openid');

        $accessToken = $this->createMock(AccessTokenEntityInterface::class);
        $accessToken->method('getScopes')->willReturn([$openidScope]);

        // Create refresh token with the access token
        $refreshToken = $this->createMock(RefreshTokenEntityInterface::class);
        $refreshToken->method('getAccessToken')->willReturn($accessToken);
        $refreshToken->expects($this->once())
            ->method('setExpiryDateTime')
            ->with($now);

        $request = $this->createMock(ServerRequestInterface::class);
        $event = new RequestRefreshTokenEvent('refresh_token.issued', $request, $refreshToken);
        $listener->onRefreshTokenIssued($event);
    }

    public function testListenerDoesNotExpireRefreshTokenWhenOfflineAccessIsRequested(): void
    {
        $clock = $this->createMock(ClockInterface::class);
        $clock->expects($this->never())->method('now');

        $listener = new RefreshTokenOfflineAccessExpiryListener(true, $clock);

        // Create access token with both openid and offline_access scopes
        $openidScope = $this->createMock(ScopeEntityInterface::class);
        $openidScope->method('getIdentifier')->willReturn('openid');

        $offlineAccessScope = $this->createMock(ScopeEntityInterface::class);
        $offlineAccessScope->method('getIdentifier')->willReturn('offline_access');

        $accessToken = $this->createMock(AccessTokenEntityInterface::class);
        $accessToken->method('getScopes')->willReturn([$openidScope, $offlineAccessScope]);

        // Create refresh token
        $refreshToken = $this->createMock(RefreshTokenEntityInterface::class);
        $refreshToken->method('getAccessToken')->willReturn($accessToken);
        $refreshToken->expects($this->never())->method('setExpiryDateTime');

        $request = $this->createMock(ServerRequestInterface::class);
        $event = new RequestRefreshTokenEvent('refresh_token.issued', $request, $refreshToken);
        $listener->onRefreshTokenIssued($event);
    }

    public function testListenerDisabledDoesNotExpireRefreshToken(): void
    {
        $clock = $this->createMock(ClockInterface::class);
        $clock->expects($this->never())->method('now');

        $listener = new RefreshTokenOfflineAccessExpiryListener(false, $clock);

        // Create access token without offline_access
        $openidScope = $this->createMock(ScopeEntityInterface::class);
        $openidScope->method('getIdentifier')->willReturn('openid');

        $accessToken = $this->createMock(AccessTokenEntityInterface::class);
        $accessToken->method('getScopes')->willReturn([$openidScope]);

        // Create refresh token
        $refreshToken = $this->createMock(RefreshTokenEntityInterface::class);
        $refreshToken->method('getAccessToken')->willReturn($accessToken);
        $refreshToken->expects($this->never())->method('setExpiryDateTime');

        $request = $this->createMock(ServerRequestInterface::class);
        $event = new RequestRefreshTokenEvent('refresh_token.issued', $request, $refreshToken);
        $listener->onRefreshTokenIssued($event);
    }

    public function testListenerUsesCurrentTimeWhenClockNotProvided(): void
    {
        $listener = new RefreshTokenOfflineAccessExpiryListener(true, null);

        // Create access token without offline_access
        $openidScope = $this->createMock(ScopeEntityInterface::class);
        $openidScope->method('getIdentifier')->willReturn('openid');

        $accessToken = $this->createMock(AccessTokenEntityInterface::class);
        $accessToken->method('getScopes')->willReturn([$openidScope]);

        // Create refresh token
        $refreshToken = $this->createMock(RefreshTokenEntityInterface::class);
        $refreshToken->method('getAccessToken')->willReturn($accessToken);

        $beforeCall = new \DateTimeImmutable();
        $refreshToken->expects($this->once())
            ->method('setExpiryDateTime')
            ->with($this->callback(static fn ($dateTime): bool => $dateTime >= $beforeCall && $dateTime <= new \DateTimeImmutable()));

        $request = $this->createMock(ServerRequestInterface::class);
        $event = new RequestRefreshTokenEvent('refresh_token.issued', $request, $refreshToken);
        $listener->onRefreshTokenIssued($event);
    }
}
