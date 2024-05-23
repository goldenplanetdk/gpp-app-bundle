<?php

namespace GoldenPlanet\GPPAppBundle\Security;

use GoldenPlanet\Gpp\App\Installer\Validator\HmacValidator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class HmacAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly HmacValidator $validator,
        private readonly UserProviderInterface $userProvider
    ) {
    }

    /**
     * Called on every request to decide if this listener should be used.
     */
    public function supports(Request $request): bool
    {
        return $request->query->has('shop');
    }


    public function authenticate(Request $request): Passport
    {
        if (!$shop = $request->get('shop')) {
            throw new CustomUserMessageAuthenticationException('Missing shop from url');
        }

        $userBadge = new UserBadge($shop, function () use ($request, $shop) {
            $queryString = $request->server->get('QUERY_STRING');
            try {
                $this->validator->validate($queryString);
            } catch (\InvalidArgumentException) {
                throw new CustomUserMessageAuthenticationException(
                    'This action needs a valid hmac sign'
                );
            }
            return $this->userProvider->loadUserByIdentifier($shop);
        });
        return new SelfValidatingPassport($userBadge);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse('/');
    }
}
