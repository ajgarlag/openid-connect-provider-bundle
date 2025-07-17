<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Tests;

use Defuse\Crypto\Crypto;
use League\Bundle\OAuth2ServerBundle\Model\AuthorizationCode;
use League\Bundle\OAuth2ServerBundle\Tests\TestHelper as LeagueTestHelper;

final class TestHelper
{
    public static function generateEncryptedAuthCodePayload(AuthorizationCode $authCode, ?string $nonce): ?string
    {
        $encryptedAuthCodePayload = LeagueTestHelper::generateEncryptedAuthCodePayload($authCode);

        if (!\is_string($nonce)) {
            return $encryptedAuthCodePayload;
        }

        $payload = json_decode((string) LeagueTestHelper::decryptPayload($encryptedAuthCodePayload), true, \JSON_THROW_ON_ERROR);
        $payload['nonce'] = $nonce;

        return Crypto::encryptWithPassword(json_encode($payload, \JSON_THROW_ON_ERROR), LeagueTestHelper::ENCRYPTION_KEY);
    }
}
