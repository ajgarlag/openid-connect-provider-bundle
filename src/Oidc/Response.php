<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Oidc;

use Ajgarlag\Bundle\OidcProviderBundle\Event\IdTokenIssuedEvent;
use Ajgarlag\Bundle\OidcProviderBundle\Model\IdToken;
use Ajgarlag\Bundle\OidcProviderBundle\OAuth2\IdTokenGrant;
use Lcobucci\JWT\Builder;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class Response extends IdTokenResponse
{
    public function __construct(
        IdentityProviderInterface $identityProvider,
        ClaimExtractor $claimExtractor,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct($identityProvider, $claimExtractor);
    }

    protected function getBuilder(AccessTokenEntityInterface $accessToken, UserEntityInterface $userEntity): Builder
    {
        $serverHttpHostSet = false;
        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = 'localhost';
            $serverHttpHostSet = true;
        }

        // Add required id_token claims
        $builder = parent::getBuilder($accessToken, $userEntity);

        if ($serverHttpHostSet) {
            unset($_SERVER['HTTP_HOST']);
        }

        $issuer = $this->getIssuer();
        if (\is_string($issuer)) {
            $builder = $builder->issuedBy($issuer);
        }

        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return $builder;
        }

        if ('authorization_code' === $request->request->getString('grant_type') && $request->request->has('code')) {
            $payload = json_decode($this->decrypt($request->request->getString('code')), true, \JSON_THROW_ON_ERROR);
            if (isset($payload['nonce'])) {
                $builder = $builder->withClaim('nonce', (string) $payload['nonce']);
            }
        } elseif (\in_array($request->query->getString('response_type'), IdTokenGrant::RESPONSE_TYPES, true) && $request->query->has('nonce')) {
            $builder = $builder->withClaim('nonce', $request->query->getString('nonce'));
        }

        return $builder;
    }

    private function getIssuer(): ?string
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return null;
        }

        return $request->getSchemeAndHttpHost();
    }

    /**
     * @return array{id_token?: non-empty-string}
     */
    protected function getExtraParams(AccessTokenEntityInterface $accessToken): array
    {
        /** @var array{id_token?: non-empty-string} $extraParams */
        $extraParams = parent::getExtraParams($accessToken);

        if (isset($extraParams['id_token'])) {
            $idToken = IdToken::fromString($extraParams['id_token']);
            $this->eventDispatcher->dispatch(new IdTokenIssuedEvent($idToken));
        }

        return $extraParams;
    }

    public function buildIdToken(AccessTokenEntityInterface $accessToken): string
    {
        $extraParams = $this->getExtraParams($accessToken);
        if (!isset($extraParams['id_token'])) {
            throw new \LogicException();
        }

        return $extraParams['id_token'];
    }
}
