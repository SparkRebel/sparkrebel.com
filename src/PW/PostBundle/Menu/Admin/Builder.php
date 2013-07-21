<?php

namespace PW\PostBundle\Menu\Admin;

use Knp\Menu\FactoryInterface,
    Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function statusMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());
        $menu->setChildrenAttribute('class', 'adminSubnav');

        $menu->addChild('All', array(
            'route' => 'admin_post_index',
            'routeParameters' => array(
                'status' => 'all'
            ),
        ));

        $menu->addChild('Active', array(
            'route' => 'admin_post_index',
            'routeParameters' => array(
                'status' => 'active'
            ),
        ));

        $menu->addChild('Deleted', array(
            'route' => 'admin_post_index',
            'routeParameters' => array(
                'status' => 'deleted'
            ),
        ));

        return $menu;
    }
}
