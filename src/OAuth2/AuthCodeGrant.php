<?php

declare(strict_types=1);

/*
 * Copyright (c) 2019-2020 Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgarlag\Bundle\OidcProviderBundle\OAuth2;

use League\OAuth2\Server\Grant\AuthCodeGrant as LeagueAuthCodeGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use League\OAuth2\Server\ResponseTypes\RedirectResponse;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class AuthCodeGrant extends LeagueAuthCodeGrant
{
    public function __construct(
        AuthCodeRepositoryInterface $authCodeRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        \DateInterval $authCodeTTL,
        private readonly RequestStack $requestStack,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly UriFactoryInterface $uriFactory,
    ) {
        parent::__construct($authCodeRepository, $refreshTokenRepository, $authCodeTTL);
    }

    public function completeAuthorizationRequest(AuthorizationRequestInterface $authorizationRequest): ResponseTypeInterface
    {
        $response = parent::completeAuthorizationRequest($authorizationRequest);
        if (!$response instanceof RedirectResponse) {
            return $response;
        }

        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return $response;
        }

        if (!$request->query->has('nonce')) {
            return $response;
        }

        $psr7Response = $response->generateHttpResponse($this->responseFactory->createResponse());
        $psr7Uri = $this->uriFactory->createUri($psr7Response->getHeaderLine('Location'));
        $queryParams = [];
        parse_str($psr7Uri->getQuery(), $queryParams);

        if (!isset($queryParams['code']) || !\is_string($queryParams['code'])) {
            return $response;
        }

        $payload = json_decode($this->decrypt($queryParams['code']), true, \JSON_THROW_ON_ERROR);
        $payload['nonce'] = $request->query->getString('nonce');
        $queryParams['code'] = $this->encrypt(json_encode($payload, \JSON_THROW_ON_ERROR));
        $response->setRedirectUri($psr7Uri->withQuery(http_build_query($queryParams))->__toString());

        return $response;
    }
}
