<?php

namespace PW\InviteBundle\Menu\Admin;

use Knp\Menu\FactoryInterface,
    Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function requestsMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());
        $menu->setChildrenAttribute('class', 'adminSubnav');

        $menu->addChild('Pending', array(
            'route' => 'admin_invite_request_index',
            'routeParameters' => array('status' => 'pending'),
        ));

        $menu->addChild('Assigned', array(
            'route' => 'admin_invite_request_index',
            'routeParameters' => array('status' => 'assigned'),
        ));

        $menu->addChild('Registered', array(
            'route' => 'admin_invite_request_index',
            'routeParameters' => array('status' => 'registered'),
        ));

        return $menu;
    }

    public function codesMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());
        $menu->setChildrenAttribute('class', 'adminSubnav');

        $menu->addChild('Unused', array(
            'route' => 'admin_invite_code_index',
            'routeParameters' => array('status' => 'unused'),
        ));

        $menu->addChild('Exhausted', array(
            'route' => 'admin_invite_code_index',
            'routeParameters' => array('status' => 'exhausted'),
        ));

        $menu->addChild('Active', array(
            'route' => 'admin_invite_code_index',
            'routeParameters' => array('status' => 'active'),
        ));

        return $menu;
    }
}
