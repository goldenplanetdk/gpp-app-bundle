<?php

namespace GoldenPlanet\GPPAppBundle\Controller;

use GoldenPlanet\Silex\Obb\App\AuthorizeHandler;
use GoldenPlanet\Silex\Obb\App\UninstalledSuccess;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
        $a = $this->container->get('GoldenPlanet\Silex\Obb\App\AuthorizeHandler');
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

    /**
     * @Route("/unauthorize", name="oauth_un_authorize")
     * @Method("POST")
     *
     * @param Request $request
     * @param EventDispatcher $dispatcher
     * @param LoggerInterface $logger
     * @return Response
     */
    public function unAuthorizeAction(Request $request, EventDispatcher $dispatcher, LoggerInterface $logger)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return new Response('Bad request', 404);
        }

        $event = new UninstalledSuccess($data);
        $logger->debug('removing app');
        $dispatcher->dispatch('app.uninstalled', $event);
        $logger->debug('app removed');

        return new Response('Success');
    }

}
