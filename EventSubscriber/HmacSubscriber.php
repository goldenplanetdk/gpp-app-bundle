<?php

namespace GoldenPlanet\GPPAppBundle\EventSubscriber;

use GoldenPlanet\Gpp\App\Installer\Validator\HmacValidator;
use GoldenPlanet\GPPAppBundle\Controller\HmacAuthenticatedController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HmacSubscriber implements EventSubscriberInterface
{
    /**
     * @var HmacValidator
     */
    private $validator;

    /**
     * HmacSubscriber constructor.
     */
    public function __construct(HmacValidator $validator)
    {
        $this->validator = $validator;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof HmacAuthenticatedController) {
            $session = $event->getRequest()->getSession();
            $shop = $event->getRequest()->query->get('shop', '');
            if ($session->has('shop') && ($shop === $session->get('shop') || !$shop)) {
                return; // already checked for this session
            }
            $queryString = $event->getRequest()->server->get('QUERY_STRING');
            try {
                $this->validator->validate($queryString);
                $event->getRequest()->getSession()->set('shop', $event->getRequest()->query->get('shop'));
            } catch (\InvalidArgumentException $exception) {
                 throw new AccessDeniedHttpException('This action needs a valid hmac sign');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }
}
