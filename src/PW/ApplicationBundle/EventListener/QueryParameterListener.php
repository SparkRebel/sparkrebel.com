<?php

namespace PW\ApplicationBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Pulls some query parameters out of the request and pushes
 * them into the session.
 *
 * @author Chris Jones <leeked@gmail.com>
 */
class QueryParameterListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param  GetResponseEvent $event
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        /* @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $event->getRequest();

        if ($session = $request->getSession() /* @var \Symfony\Component\HttpFoundation\Session $session */) {
            $utm    = (array) $session->get('utm_parameters', array());
            $params = (array) $request->query->all();
            foreach ($params as $key => $value) {
                if (stripos($key, 'utm_') !== false) {
                    $utm[$key] = $value;
                }
            }
            $session->set('utm_parameters', $utm);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 256),
        );
    }
}
