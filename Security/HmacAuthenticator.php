<?php

namespace GoldenPlanet\GPPAppBundle\Security;

use GoldenPlanet\Gpp\App\Installer\Validator\HmacValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class HmacAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{

    /**
     * @var HmacValidator
     */
    private $validator;

    public function __construct(HmacValidator $validator)
    {
        $this->validator = $validator;
    }

    public function createToken(Request $request, $providerKey)
    {
        $shop = $request->query->get('shop');

        if (!$shop) {
            throw new BadCredentialsException();
        }
        $queryString = $request->server->get('QUERY_STRING');

        return new PreAuthenticatedToken(
            'anon.',
            base64_encode($shop) . ':' . base64_encode($queryString),
            $providerKey
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $credential = $token->getCredentials();
        list($username, $queryString) = explode(':', $credential);
        $username = base64_decode($username);
        $queryString = base64_decode($queryString);
        if (!$username) {
            // CAUTION: this message will be returned to the client
            // (so don't put any un-trusted messages / error strings here)
            throw new CustomUserMessageAuthenticationException(
                'Invalid formatted data for hmac validation'
            );
        }

        try {
            $this->validator->validate($queryString);
        } catch (\InvalidArgumentException $exception) {
            throw new CustomUserMessageAuthenticationException(
                'This action needs a valid hmac sign'
            );
        }

        try {
            $user = $userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            throw new CustomUserMessageAuthenticationException(
                sprintf('App with this credentials "%s" does not exist.', $username)
            );
        }

        return new PreAuthenticatedToken(
            $user,
            $queryString,
            $providerKey,
            $user->getRoles()
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response(
        // this contains information about *why* authentication failed
        // use it, or return your own message
            strtr($exception->getMessageKey(), $exception->getMessageData()),
            401
        );
    }
}
