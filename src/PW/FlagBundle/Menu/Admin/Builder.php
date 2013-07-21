<?php

namespace PW\FlagBundle\Menu\Admin;

use Knp\Menu\FactoryInterface,
    Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function reasonTypeMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());
        $menu->setChildrenAttribute('class', 'adminSubnav');

        $type       = $this->container->get('request')->get('type');
        $status     = $this->container->get('request')->get('status');

        $menu->addChild('All', array(
            'route' => 'admin_flag_index',
            'routeParameters' => array(
                'type' => $type, 'reasonType' => 'all', 'status' => $status
            ),
        ));

        $menu->addChild('Copyright', array(
            'route' => 'admin_flag_index',
            'routeParameters' => array(
                'type' => $type, 'reasonType' => 'copyright', 'status' => $status
            ),
        ));

        $menu->addChild('Inappropriate', array(
            'route' => 'admin_flag_index',
            'routeParameters' => array(
                'type' => $type, 'reasonType' => 'inappropriate', 'status' => $status
            ),
        ));

        $menu->addChild('Other', array(
            'route' => 'admin_flag_index',
            'routeParameters' => array(
                'type' => $type, 'reasonType' => 'other', 'status' => $status
            ),
        ));

        return $menu;
    }

    public function statusMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());
        $menu->setChildrenAttribute('class', 'adminSubnav');

        $type       = $this->container->get('request')->get('type');
        $reasonType = $this->container->get('request')->get('reasonType');

        $menu->addChild('Pending', array(
            'route' => 'admin_flag_index',
            'routeParameters' => array(
                'type' => $type, 'reasonType' => $reasonType, 'status' => 'pending'
            ),
        ));

        $menu->addChild('Approved', array(
            'route' => 'admin_flag_index',
            'routeParameters' => array(
                'type' => $type, 'reasonType' => $reasonType, 'status' => 'approved'
            ),
        ));

        $menu->addChild('Rejected', array(
            'route' => 'admin_flag_index',
            'routeParameters' => array(
                'type' => $type, 'reasonType' => $reasonType, 'status' => 'rejected'
            ),
        ));

        return $menu;
    }
}
