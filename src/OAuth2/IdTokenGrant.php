<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OidcProviderBundle\OAuth2;

use Ajgarlag\Bundle\OidcProviderBundle\Oidc\Response;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractAuthorizeGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use League\OAuth2\Server\ResponseTypes\RedirectResponse;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

final class IdTokenGrant extends AbstractAuthorizeGrant
{
    private const RESPONSE_TYPE_IDTOKEN = 'id_token';
    private const RESPONSE_TYPE_IDTOKEN_TOKEN = 'id_token token';
    public const RESPONSE_TYPES = [
        self::RESPONSE_TYPE_IDTOKEN,
        self::RESPONSE_TYPE_IDTOKEN_TOKEN,
    ];

    public function __construct(
        private Response $idTokenResponse,
        private \DateInterval $accessTokenTTL,
        private string $queryDelimiter = '#',
    ) {
    }

    public function getIdentifier(): string
    {
        return self::RESPONSE_TYPE_IDTOKEN;
    }

    protected function createAuthorizationRequest(): AuthorizationRequestInterface
    {
        return new AuthorizationRequest();
    }

    /**
     * @throws \LogicException
     */
    public function setRefreshTokenTTL(\DateInterval $refreshTokenTTL): void
    {
        throw new \LogicException('The Implicit Grant does not return refresh tokens');
    }

    /**
     * @throws \LogicException
     */
    public function setRefreshTokenRepository(RefreshTokenRepositoryInterface $refreshTokenRepository): void
    {
        throw new \LogicException('The Implicit Grant does not return refresh tokens');
    }

    public function canRespondToAccessTokenRequest(ServerRequestInterface $request): bool
    {
        return false;
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL,
    ): ResponseTypeInterface {
        throw new \LogicException('This grant does not used this method');
    }

    public function canRespondToAuthorizationRequest(ServerRequestInterface $request): bool
    {
        return
            isset($request->getQueryParams()['scope'])
            && isset($request->getQueryParams()['response_type'])
            && \in_array($request->getQueryParams()['response_type'], self::RESPONSE_TYPES, true)
            && isset($request->getQueryParams()['client_id'])
            && isset($request->getQueryParams()['redirect_uri'])
        ;
    }

    public function validateAuthorizationRequest(ServerRequestInterface $request): AuthorizationRequestInterface
    {
        $clientId = $this->getQueryStringParameter(
            'client_id',
            $request,
            $this->getServerParameter('PHP_AUTH_USER', $request)
        );

        if (null === $clientId) {
            throw OAuthServerException::invalidRequest('client_id');
        }

        $client = $this->getClientEntityOrFail($clientId, $request);

        $redirectUri = $this->getQueryStringParameter('redirect_uri', $request);

        if (null !== $redirectUri) {
            $this->validateRedirectUri($redirectUri, $client, $request);
        } elseif (
            '' === $client->getRedirectUri()
            || (\is_array($client->getRedirectUri()) && 1 !== \count($client->getRedirectUri()))
        ) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));
            throw OAuthServerException::invalidClient($request);
        }

        $stateParameter = $this->getQueryStringParameter('state', $request);

        $scopes = $this->validateScopes(
            $this->getQueryStringParameter('scope', $request, $this->defaultScope),
            $this->makeRedirectUri(
                $redirectUri ?? $this->getClientRedirectUri($client),
                null !== $stateParameter ? ['state' => $stateParameter] : [],
                $this->queryDelimiter
            )
        );

        $authorizationRequest = $this->createAuthorizationRequest();
        $authorizationRequest->setGrantTypeId($this->getIdentifier());
        $authorizationRequest->setClient($client);
        $authorizationRequest->setRedirectUri($redirectUri);

        if (null !== $stateParameter) {
            $authorizationRequest->setState($stateParameter);
        }

        $authorizationRequest->setScopes($scopes);

        if ($authorizationRequest instanceof AuthorizationRequest && self::RESPONSE_TYPE_IDTOKEN_TOKEN === $this->getQueryStringParameter('response_type', $request)) {
            $authorizationRequest->setAccessTokenRequired(true);
        }

        return $authorizationRequest;
    }

    public function completeAuthorizationRequest(AuthorizationRequestInterface $authorizationRequest): ResponseTypeInterface
    {
        if (false === $authorizationRequest->getUser() instanceof UserEntityInterface) {
            throw new \LogicException('An instance of UserEntityInterface should be set on the AuthorizationRequest');
        }

        $finalRedirectUri = $authorizationRequest->getRedirectUri()
                          ?? $this->getClientRedirectUri($authorizationRequest->getClient());

        // The user approved the client, redirect them back with an access token
        if (true === $authorizationRequest->isAuthorizationApproved()) {
            // Finalize the requested scopes
            $finalizedScopes = $this->scopeRepository->finalizeScopes(
                $authorizationRequest->getScopes(),
                $this->getIdentifier(),
                $authorizationRequest->getClient(),
                $authorizationRequest->getUser()->getIdentifier()
            );

            $accessToken = $this->issueAccessToken(
                $this->accessTokenTTL,
                $authorizationRequest->getClient(),
                $authorizationRequest->getUser()->getIdentifier(),
                $finalizedScopes
            );

            // TODO: next major release: this method needs `ServerRequestInterface` as an argument
            // $this->getEmitter()->emit(new RequestAccessTokenEvent(RequestEvent::ACCESS_TOKEN_ISSUED, $request, $accessToken));

            $idTokenResponse = clone $this->idTokenResponse;
            $idTokenResponse->setPrivateKey($this->privateKey);

            $accessTokenParams = [];
            if ($authorizationRequest instanceof AuthorizationRequest && $authorizationRequest->isAccessTokenRequired()) {
                $accessTokenParams = [
                    'access_token' => $accessToken->toString(),
                    'token_type' => 'Bearer',
                    'expires_in' => $accessToken->getExpiryDateTime()->getTimestamp() - time(),
                ];
            }
            $idTokenParams = [
                'id_token' => $idTokenResponse->buildIdToken($accessToken),
                'state' => $authorizationRequest->getState(),
            ];

            $response = new RedirectResponse();
            $response->setRedirectUri(
                $this->makeRedirectUri(
                    $finalRedirectUri,
                    array_merge($accessTokenParams, $idTokenParams),
                    $this->queryDelimiter
                )
            );

            return $response;
        }

        // The user denied the client, redirect them back with an error
        throw OAuthServerException::accessDenied('The user denied the request', $this->makeRedirectUri($finalRedirectUri, ['state' => $authorizationRequest->getState()], $this->queryDelimiter));
    }
}
