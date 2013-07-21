<?php

namespace PW\ApplicationBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RouteStorageListener
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $router;

    public function __construct(\Symfony\Component\Routing\Router $router)
    {
        $this->router = $router;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== \Symfony\Component\HttpKernel\HttpKernel::MASTER_REQUEST) {
            return;
        }

        /** @var \Symfony\Component\HttpFoundation\Request $request  */
        $request = $event->getRequest();
        /** @var \Symfony\Component\HttpFoundation\Session $session  */
        $session = $request->getSession();

        $routeQuery  = $request->query->all();
        $routeParams = $this->router->match($request->getPathInfo());
        $routeName   = $routeParams['_route'];
        if ($routeName[0] == '_') {
            return;
        }
        unset($routeParams['_route']);

        $routeData = array(
            'name'   => $routeName,
            'params' => array_merge($routeQuery, $routeParams),
        );

        // Skipping duplicates
        $thisRoute = $session->get('this_route', array());

        if ($thisRoute == $routeData) {
            return;
        }
        $session->set('last_route', $thisRoute);
        $session->set('this_route', $routeData);
    }
}
