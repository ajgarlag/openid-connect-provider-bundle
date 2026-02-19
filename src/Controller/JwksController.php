<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller;

use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\CryptKeyInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class JwksController
{
    private CryptKeyInterface $publicKey;

    public function __construct(
        CryptKeyInterface|string $publicKey,
    ) {
        $this->publicKey = \is_string($publicKey) ? new CryptKey($publicKey) : $publicKey;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $openSslAsymmetricKey = openssl_pkey_get_public($this->publicKey->getKeyContents());
        if (false === $openSslAsymmetricKey) {
            throw new \RuntimeException('Cannot get public key');
        }

        $keyDetails = openssl_pkey_get_details($openSslAsymmetricKey);
        if (false === $keyDetails) {
            throw new \RuntimeException('Cannot get key details');
        }

        return new JsonResponse(
            [
                'keys' => [[
                    'kty' => 'RSA',
                    'n' => rtrim(strtr(base64_encode((string) $keyDetails['rsa']['n']), '+/', '-_'), '='),
                    'e' => rtrim(strtr(base64_encode((string) $keyDetails['rsa']['e']), '+/', '-_'), '='),
                ]],
            ],
            JsonResponse::HTTP_OK,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }
}
