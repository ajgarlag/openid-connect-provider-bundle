<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model;

use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;

interface RelyingPartyInterface
{
    /**
     * @return non-empty-string
     */
    public function getIdentifier(): string;

    public function getClient(): ClientInterface;

    /**
     * @return list<RedirectUri>
     */
    public function getPostLogoutRedirectUris(): array;

    public function setPostLogoutRedirectUris(RedirectUri ...$postLogoutRedirectUris): self;
}
