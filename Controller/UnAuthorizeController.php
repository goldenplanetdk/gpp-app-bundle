<?php

namespace GoldenPlanet\GPPAppBundle\Controller;

use GoldenPlanet\Gpp\App\Installer\UninstalledSuccess;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/oauth")
 */
class UnAuthorizeController extends AbstractController implements WebhookAuthenticatedController
{

    /**
     * @Route("/unauthorize", name="oauth_un_authorize", methods={"POST"})
     *
     * @param Request $request
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface $logger
     * @return Response
     */
    public function unAuthorizeAction(Request $request, EventDispatcherInterface $dispatcher, LoggerInterface $logger)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return new Response('Bad request', \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
        }

        $event = new UninstalledSuccess($data);
        $logger->debug('removing app');
        $dispatcher->dispatch('app.uninstalled', $event);
        $logger->debug('app removed');

        return new Response('Success');
    }
}
