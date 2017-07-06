<?php

namespace GoldenPlanet\GPPAppBundle\EventSubscriber;

use GoldenPlanet\Gpp\App\Installer\Validator\WebhookValidator;
use GoldenPlanet\GPPAppBundle\Controller\WebhookAuthenticatedController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class WebhookSubscriber implements EventSubscriberInterface
{
    /**
     * @var WebhookValidator
     */
    private $validator;

    /**
     * WebhookSubscriber constructor.
     * @param WebhookValidator $validator
     */
    public function __construct(WebhookValidator $validator)
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

        if ($controller[0] instanceof WebhookAuthenticatedController) {
            // webhook validation
            $payload = $event->getRequest()->getContent();
            try {
                $this->validator->validate($payload, $event->getRequest()->headers->get('X-OBB-SIGNATURE'));
            } catch (\InvalidArgumentException $exception) {
                 throw new AccessDeniedHttpException('This action needs a valid signature');
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
