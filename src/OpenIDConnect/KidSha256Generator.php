<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect;

use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\CryptKeyInterface;

final readonly class KidSha256Generator implements \Stringable
{
    private CryptKeyInterface $key;

    public function __construct(
        string|CryptKeyInterface $key,
    ) {
        if (false === $key instanceof CryptKeyInterface) {
            $key = new CryptKey($key);
        }
        $this->key = $key;
    }

    public function __toString(): string
    {
        return hash('sha256', $this->key->getKeyContents());
    }
}
