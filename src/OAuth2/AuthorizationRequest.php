<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\OAuth2;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest as LeagueAuthorizationRequest;

final class AuthorizationRequest extends LeagueAuthorizationRequest
{
    private bool $accessTokenRequired = false;

    public function isAccessTokenRequired(): bool
    {
        return $this->accessTokenRequired;
    }

    public function setAccessTokenRequired(bool $accessTokenRequired): bool
    {
        return $this->accessTokenRequired = $accessTokenRequired;
    }
}
