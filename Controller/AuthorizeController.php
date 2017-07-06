<?php

namespace GoldenPlanet\GPPAppBundle\Controller;

use GoldenPlanet\Gpp\App\Installer\AuthorizeHandler;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Route("/oauth")
 */
class AuthorizeController extends Controller implements HmacAuthenticatedController
{

    /**
     * @Route("/authorize", name="oauth_authorize")
     * @Method("GET")
     *
     * @param Request $request
     * @param AuthorizeHandler $authHandler
     * @param LoggerInterface $logger
     * @param Session $session
     * @return RedirectResponse|Response
     */
    public function authorizeAction(LoggerInterface $logger, Session $session, Request $request, AuthorizeHandler $authHandler= null)
    {
        $shop = $request->query->get('shop');
        $code = $request->query->get('code');
        $isSecure = $request->query->get('https', 0);
        $proto = $isSecure ? 'https' : 'http';

        if (!preg_match('#^[a-z0-9.-]+$#', $shop)) {
            throw new \InvalidArgumentException('Invalid shop value');
        }

        if ($shop && !$code) {
            $logger->debug('first round');
            // Step 1: get the shopname from the user and redirect the user to the
            // obb authorization page where they can choose to authorize this app

            $bytes = random_bytes(24);
            $state = bin2hex($bytes);

            $url = $authHandler->generateAuthorizeUrl($shop, $state, $proto);

            $session->set('shop', $shop);
            $session->set('state', $state);

            // redirect to authorize url
            return new RedirectResponse($url);
        } elseif ($code) {
            // Step 2: do a form POST to get the access token
            /** @var Session $session */
            $state = $request->query->get('state');
            if (!$state || $state !== $session->get('state')) {
                throw new \InvalidArgumentException('State for this request is incorrect');
            }

            $session->clear();
            $token = $authHandler->token($shop, $code, $proto);

            // Now, request the token and store it in your session.
            return new RedirectResponse($proto . '://' . $shop . '/admin/apps/');
        } else {
            return new Response('Invalid request');
        }
    }
}
