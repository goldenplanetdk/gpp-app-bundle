<?php

namespace GoldenPlanet\GPPAppBundle\Security;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * In case if two store already authenticated remove token from session and try auth one more time
 *
 * Class HmacAuthenticatorListener
 * @package GoldenPlanet\PriceMonitoring\Infrastructure\AppBundle\Security
 */
class HmacAuthenticatorListener
{

    // firewall key in security.yml

    /**
     * HmacAuthenticatorListener constructor.
     */
    public function __construct(private $firewallKey = 'secured_area')
    {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $firewall = '_security_' . $this->firewallKey;
        $data = unserialize($request->getSession()->get($firewall));
        $shop = $request->get('shop');

        if (!($data instanceof TokenInterface) || !$data || !$shop) {
            return;
        }

        if (!$data->getUser()) {
            return;
        }

        if ($shop !== $data->getUser()->domain()) {
            $request->getSession()->remove($firewall);
        }
    }
}
