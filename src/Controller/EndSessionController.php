<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Logout\PostLogoutRedirectUriStorageInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\RelyingPartyManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\IdToken;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\IdTokenInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\OpenIDConnect\SessionSidManager;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\ClientInterface;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\OAuth2\Server\CryptKeyInterface;
use League\OAuth2\Server\RedirectUriValidators\RedirectUriValidator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Twig\Environment;

final readonly class EndSessionController
{
    public function __construct(
        private LogoutUrlGenerator $logoutUrlGenerator,
        private ClientManagerInterface $clientManager,
        private RelyingPartyManagerInterface $relyingPartyManager,
        private CryptKeyInterface $publicKey,
        private PostLogoutRedirectUriStorageInterface $redirectUriStorage,
        private Security $security,
        private SessionSidManager $sessionSidManager,
        private Environment $twigEnvironment,
        private HttpUtils $httpUtils,
        private string $cancelLogoutDefaultPath,
    ) {
    }

    /**
     * @param string|null $logoutHint session ID to logout
     */
    public function __invoke(
        Request $request,
        #[MapQueryParameter('id_token_hint')] ?string $idTokenHint,
        #[MapQueryParameter('logout_hint')] ?string $logoutHint,
        #[MapQueryParameter('client_id')] ?string $clientId,
        #[MapQueryParameter('post_logout_redirect_uri')] ?string $postLogoutRedirectUri,
        #[MapQueryParameter('state')] ?string $state,
        ?UserInterface $user,
    ): Response {
        $this->assertPostLogoutRedirectUriRequirements($postLogoutRedirectUri, $idTokenHint, $clientId);

        $idToken = $this->resolveIdToken($idTokenHint);
        if ($idToken instanceof IdTokenInterface) {
            $this->assertIdTokenIsValid($request, $idToken);
        }

        $client = $this->resolveClient($clientId, $idToken);
        $validatedRedirectUri = $this->resolveRedirectUriForClient($postLogoutRedirectUri, $client);

        $validatedRedirectUriWithState = $this->appendStateToRedirectUri($validatedRedirectUri, $state);
        $this->cacheRedirectUri($validatedRedirectUriWithState, $request);

        $cancelLogoutUri = $this->getCancelLogoutUrl($validatedRedirectUriWithState, $request);

        if (null === $user) {
            return new RedirectResponse($cancelLogoutUri);
        }

        if (
            $this->isConfirmationNeeded($clientId, $idToken, $client, $validatedRedirectUri)
            || $this->shouldForceConfirmation($clientId, $idToken, $client, $logoutHint, $request)
        ) {
            return new Response($this->twigEnvironment->render('@AjgarlagOpenIDConnectProvider/end_session.html.twig', ['cancel_logout_uri' => $cancelLogoutUri]));
        }

        return new RedirectResponse($this->logoutUrlGenerator->getLogoutPath());
    }

    private function assertPostLogoutRedirectUriRequirements(?string $postLogoutRedirectUri, ?string $idTokenHint, ?string $clientId): void
    {
        if (\is_string($postLogoutRedirectUri) && null === $idTokenHint && null === $clientId) {
            throw new BadRequestException('Either the parameter "client_id" or the parameter "id_token_hint" is required when "post_logout_redirect_uri" is used.');
        }
    }

    private function resolveClient(?string $clientId, ?IdTokenInterface $idToken): ?ClientInterface
    {
        if ($idToken instanceof IdTokenInterface) {
            $authorizedParty = $this->resolveAuthorizedParty($idToken);
            if (\is_string($clientId) && \is_string($authorizedParty) && $authorizedParty !== $clientId) {
                throw new BadRequestException('Parameter client_id is different than the client for which ID Token was issued.');
            }
            if (null === $clientId && \is_string($authorizedParty)) {
                return $this->clientManager->find($authorizedParty);
            }
        }
        if (\is_string($clientId)) {
            return $this->clientManager->find($clientId);
        }

        return null;
    }

    private function resolveIdToken(?string $idTokenHint): ?IdTokenInterface
    {
        return \is_string($idTokenHint) && '' !== $idTokenHint ? IdToken::fromString($idTokenHint) : null;
    }

    private function resolveAuthorizedParty(?IdTokenInterface $idToken): ?string
    {
        if ($idToken instanceof IdTokenInterface) {
            return $idToken->getAuthorizedParty() ?? (1 === \count($idToken->getAudience()) ? current($idToken->getAudience()) : null);
        }

        return null;
    }

    private function shouldForceConfirmation(?string $clientId, ?IdTokenInterface $idToken, ?ClientInterface $client, ?string $logoutHint, Request $request): bool
    {
        if (\is_string($clientId) && null === $client) {
            return true;
        }

        if (!$request->hasSession()) {
            return false;
        }

        if (null === $firewallConfig = $this->security->getFirewallConfig($request)) {
            return false;
        }

        $sid = $this->sessionSidManager->getSid($firewallConfig);

        if (\is_string($logoutHint) && $sid !== $logoutHint) {
            return true;
        }

        if ($idToken instanceof IdTokenInterface && null !== $idTokenSid = $idToken->getClaim('sid')) {
            return $sid !== $idTokenSid;
        }

        return false;
    }

    private function isConfirmationNeeded(?string $clientId, ?IdTokenInterface $idToken, ?ClientInterface $client, ?string $validatedRedirectUri): bool
    {
        if ($idToken instanceof IdTokenInterface && \is_string($clientId) && $clientId === $this->resolveAuthorizedParty($idToken) && $clientId === $client?->getIdentifier()) {
            return false;
        }

        if (null === $idToken && $client instanceof ClientInterface && \is_string($validatedRedirectUri)) {
            return false;
        }

        return true;
    }

    private function resolveRedirectUriForClient(?string $postLogoutRedirectUri, ?ClientInterface $client): ?string
    {
        if (null === $postLogoutRedirectUri || null === $client) {
            return null;
        }
        $relyingParty = $this->relyingPartyManager->get($client);
        $validator = new RedirectUriValidator(array_map(static fn (RedirectUri $redirectUri) => $redirectUri->__toString(), $relyingParty->getPostLogoutRedirectUris()));
        if (!$validator->validateRedirectUri($postLogoutRedirectUri)) {
            throw new BadRequestException('Invalid "post_logout_redirect_uri" parameter.');
        }

        return $postLogoutRedirectUri;
    }

    private function appendStateToRedirectUri(?string $validatedRedirectUri, ?string $state): ?string
    {
        if (null === $validatedRedirectUri || null === $state) {
            return $validatedRedirectUri;
        }

        return $validatedRedirectUri . (!str_contains($validatedRedirectUri, '?') ? '?' : '&') . http_build_query(['state' => $state]);
    }

    private function cacheRedirectUri(?string $validatedRedirectUri, Request $request): void
    {
        if (null === $validatedRedirectUri) {
            return;
        }

        if (null === $firewallConfig = $this->security->getFirewallConfig($request)) {
            return;
        }

        $this->redirectUriStorage->save($firewallConfig->getName(), $validatedRedirectUri);
    }

    private function getCancelLogoutUrl(?string $validatedRedirectUri, Request $request): string
    {
        if (\is_string($validatedRedirectUri)) {
            return $validatedRedirectUri;
        }

        return $this->httpUtils->generateUri($request, $this->cancelLogoutDefaultPath);
    }

    private function assertIdTokenIsValid(Request $request, IdTokenInterface $idToken): void
    {
        $jwtConfiguration = $this->getJwtConfiguration($request);

        try {
            $token = $jwtConfiguration->parser()->parse($idToken->__toString());
        } catch (\Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        try {
            // Attempt to validate the JWT
            $constraints = $jwtConfiguration->validationConstraints();
            $jwtConfiguration->validator()->assert($token, ...$constraints);
        } catch (RequiredConstraintsViolated $exception) {
            throw new BadRequestHttpException('Access token could not be verified', $exception);
        }

        if (!$token instanceof UnencryptedToken) {
            throw new BadRequestHttpException('Access token is not an instance of UnencryptedToken');
        }
    }

    private function getJwtConfiguration(Request $request): Configuration
    {
        $jwtConfiguration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('empty', 'empty')
        );

        $publicKeyContents = $this->publicKey->getKeyContents();
        \assert('' !== $publicKeyContents);

        $issuer = $request->getSchemeAndHttpHost() . $request->getBasePath();
        \assert('' !== $issuer);

        $jwtConfiguration->setValidationConstraints(
            new SignedWith(
                new Sha256(),
                InMemory::plainText($publicKeyContents, $this->publicKey->getPassPhrase() ?? '')
            ),
            new IssuedBy($issuer),
        );

        return $jwtConfiguration;
    }
}
