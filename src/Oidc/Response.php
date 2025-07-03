<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\Oidc;

use Ajgarlag\Bundle\OidcProviderBundle\Event\IdTokenIssuedEvent;
use Ajgarlag\Bundle\OidcProviderBundle\Model\IdToken;
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
