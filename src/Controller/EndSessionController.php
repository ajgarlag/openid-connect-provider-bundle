<?php

declare(strict_types=1);

namespace Ajgarlag\Bundle\OpenIDConnectProviderBundle\Controller;

use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Manager\ClientExtensionManagerInterface;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\IdToken;
use Ajgarlag\Bundle\OpenIDConnectProviderBundle\Model\IdTokenInterface;
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
use Psr\Cache\CacheItemPoolInterface;
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

final class EndSessionController
{
    public function __construct(
        private readonly LogoutUrlGenerator $logoutUrlGenerator,
        private readonly ClientManagerInterface $clientManager,
        private readonly ClientExtensionManagerInterface $clientExtensionManager,
        private readonly CryptKeyInterface $publicKey,
        private readonly CacheItemPoolInterface $cache,
        private readonly Environment $twigEnvironment,
        private readonly HttpUtils $httpUtils,
        private readonly string $cancelLogoutPath = '/',
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryParameter('id_token_hint')] ?string $idTokenHint,
        #[MapQueryParameter('logout_hint')] ?string $logoutHint,
        #[MapQueryParameter('client_id')] ?string $clientId,
        #[MapQueryParameter('post_logout_redirect_uri')] ?string $postLogoutRedirectUri,
        #[MapQueryParameter('state')] ?string $state,
        ?UserInterface $user,
    ): Response {
        if (\is_string($postLogoutRedirectUri) && null === $idTokenHint && null === $clientId) {
            throw new BadRequestException('Either the parameter "client_id" or the parameter "id_token_hint" is required when "post_logout_redirect_uri" is used.');
        }

        $confirmationNeeded = true;
        $forcedConfirmation = false;

        $client = \is_string($clientId) ? $this->clientManager->find($clientId) : null;
        if (null === $client) {
            $forcedConfirmation = true;
        }

        $idToken = \is_string($idTokenHint) && '' !== $idTokenHint ? IdToken::fromString($idTokenHint) : null;
        if ($idToken instanceof IdTokenInterface) {
            $this->validateIdToken($request, $idToken);
        }

        $authorizedParty = $idToken instanceof IdTokenInterface ? ($idToken->getAuthorizedParty() ?? (1 === \count($idToken->getAudience()) ? current($idToken->getAudience()) : null)) : null;
        if (null === $clientId) {
            if (\is_string($authorizedParty)) {
                $client = $this->clientManager->find($authorizedParty);
                if ($client instanceof ClientInterface) {
                    $forcedConfirmation = true;
                }
            }
        } elseif (\is_string($authorizedParty)) {
            if ($authorizedParty === $clientId) {
                $confirmationNeeded = false;
            } else {
                throw new BadRequestException('Parameter client_id is different than the client for which ID Token was issued.');
            }
        }

        $validatedRedirectUri = null;
        if (\is_string($postLogoutRedirectUri) && $client instanceof ClientInterface) {
            $clientExtension = $this->clientExtensionManager->get($client);
            $validator = new RedirectUriValidator(array_map(fn (RedirectUri $redirectUri) => $redirectUri->__toString(), $clientExtension->getPostLogoutRedirectUris()));
            if ($validator->validateRedirectUri($postLogoutRedirectUri)) {
                $validatedRedirectUri = $postLogoutRedirectUri;
            } else {
                throw new BadRequestException('Invalid "post_logout_redirect_uri" parameter.');
            }
        }

        if ($user instanceof UserInterface && $request->hasSession()) {
            if (
                ($idToken instanceof IdTokenInterface && $request->getSession()->getId() !== $idToken->getClaim('sid'))
                || \is_string($logoutHint) && $request->getSession()->getId() !== $logoutHint
            ) {
                $forcedConfirmation = true;
            }
        } elseif (null === $idToken && $client instanceof ClientInterface && \is_string($validatedRedirectUri)) {
            $confirmationNeeded = false;
        }

        if (\is_string($validatedRedirectUri)) {
            if (\is_string($state)) {
                $validatedRedirectUri .= (!str_contains('?', $validatedRedirectUri) ? '?' : '&') . http_build_query(['state' => $state]);
            }

            $item = $this->cache->getItem('ajgarlag.openid-connect-provider.logout.' . $request->attributes->get('_firewall_context'));
            $item->set($validatedRedirectUri);
            $item->expiresAfter(60);
            $this->cache->save($item);
        }

        $cancelLogoutUrl = \is_string($validatedRedirectUri) ? $validatedRedirectUri : $this->httpUtils->generateUri($request, $this->cancelLogoutPath);

        if (null === $user) {
            return new RedirectResponse($cancelLogoutUrl);
        }

        if ($confirmationNeeded || $forcedConfirmation) {
            return new Response($this->twigEnvironment->render('@AjgarlagOpenIDConnectProvider/end_session.html.twig', ['cancel_logout_url' => $cancelLogoutUrl]));
        }

        return new RedirectResponse($this->logoutUrlGenerator->getLogoutPath());
    }

    private function validateIdToken(Request $request, IdTokenInterface $idToken): void
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
