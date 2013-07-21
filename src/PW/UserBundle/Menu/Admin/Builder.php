<?php

namespace PW\UserBundle\Menu\Admin;

use Knp\Menu\FactoryInterface,
    Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function usersMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());
        $menu->setChildrenAttribute('class', 'adminSubnav');

        $menu->addChild('Users', array(
            'route' => 'admin_user_index',
            'routeParameters' => array('type' => 'user'),
        ));

        $menu->addChild('Brands', array(
            'route' => 'admin_user_index',
            'routeParameters' => array('type' => 'brand'),
        ));

        $menu->addChild('Merchants', array(
            'route' => 'admin_user_index',
            'routeParameters' => array('type' => 'merchant'),
        ));
        
        $menu->addChild('Interns', array(
            'route' => 'admin_intern_boards',
            'routeParameters' => array(),
        ));

        return $menu;
    }

    public function statusMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());
        $menu->setChildrenAttribute('class', 'adminSubnav');

        $menu->addChild('All', array(
            'route' => 'admin_user_index',
            'routeParameters' => array(
                'status' => 'all'
            ),
        ));

        $menu->addChild('Active', array(
            'route' => 'admin_user_index',
            'routeParameters' => array(
                'status' => 'active'
            ),
        ));

        $menu->addChild('Deleted', array(
            'route' => 'admin_user_index',
            'routeParameters' => array(
                'status' => 'deleted'
            ),
        ));

        return $menu;
    }
}
