<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Model;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\Entities\ClaimSetInterface;

class Identity implements UserEntityInterface, ClaimSetInterface
{
    use EntityTrait;

    /**
     * @var array<non-empty-string, mixed>
     */
    private $claims = [];

    /**
     * @return array<non-empty-string, mixed>
     */
    public function getClaims(): array
    {
        return $this->claims;
    }

    /**
     * @param array<non-empty-string, mixed> $claims
     */
    public function setClaims(array $claims): void
    {
        $this->claims = $claims;
    }
}
