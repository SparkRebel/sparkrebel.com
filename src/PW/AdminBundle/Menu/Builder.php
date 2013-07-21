<?php

namespace PW\AdminBundle\Menu;

use Knp\Menu\FactoryInterface,
    Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());
        $menu->setChildrenAttribute('id', 'nav');

        $menu->addChild('Items', array(
            'route' => null
        ));
        $menu['Items']->addChild('Brand/Store aliases', array(
            'route' => 'admin_alias_index',
        ));
        $menu['Items']->addChild('Brand Whitelist', array(
            'route' => 'admin_whitelist_index',
        ));

        $menu->addChild('Cms', array(
            'route' => 'admin_cms_page_index',
        ));

        $menu->addChild('Flagged', array(
            'route' => null
        ));
        $menu['Flagged']->addChild('Posts', array(
            'route' => 'admin_flag_index',
            'routeParameters' => array('type' => 'posts')
        ));
        $menu['Flagged']->addChild('Comments', array(
            'route' => 'admin_flag_index',
            'routeParameters' => array('type' => 'comments')
        ));

        $menu->addChild('Users', array(
            'route' => 'admin_user_index'
        ));
        $menu['Users']->addChild('Brands', array(
            'route' => 'admin_user_index',
            'routeParameters' => array('type' => 'brand')
        ));
        $menu['Users']->addChild('Merchants', array(
            'route' => 'admin_user_index',
            'routeParameters' => array('type' => 'merchant')
        ));

        $menu['Users']->addChild('Partners', array(
            'route' => 'admin_user_partner_index',
            'routeParameters' => array('status' => 'pending')
        ));
        $menu['Users']['Partners']->addChild('Approved', array(
            'route' => 'admin_user_partner_index',
            'routeParameters' => array('status' => 'approved')
        ));
        $menu['Users']['Partners']->addChild('Rejected', array(
            'route' => 'admin_user_partner_index',
            'routeParameters' => array('status' => 'rejected')
        ));

        $menu->addChild('Invite Requests', array(
            'route' => 'admin_invite_request_index',
            'routeParameters' => array('status' => 'pending')
        ));
        $menu['Invite Requests']->addChild('Assigned', array(
            'route' => 'admin_invite_request_index',
            'routeParameters' => array('status' => 'assigned')
        ));
        $menu['Invite Requests']->addChild('Registered', array(
            'route' => 'admin_invite_request_index',
            'routeParameters' => array('status' => 'registered')
        ));

        $menu->addChild('Invite Codes', array(
            'route' => 'admin_invite_code_index',
            'routeParameters' => array('status' => 'active')
        ));
        $menu['Invite Codes']->addChild('Unused', array(
            'route' => 'admin_invite_code_index',
            'routeParameters' => array('status' => 'unused')
        ));
        $menu['Invite Codes']->addChild('Exhausted', array(
            'route' => 'admin_invite_code_index',
            'routeParameters' => array('status' => 'exhausted')
        ));

        $menu->addChild('Signup', array(
            'route' => null
        ));
        $menu['Signup']->addChild('Areas', array(
            'route' => 'admin_signup_areas'
        ));
        $menu['Signup']->addChild('Categories', array(
            'route' => 'admin_signup_categories'
        ));
        
        $menu->addChild('Featured', array(
            'route' => null
        ));
        $menu['Featured']->addChild('Brands', array(
            'route' => 'admin_feature_brand_index'
        ));
        $menu['Featured']->addChild('Collections', array(
            'route' => 'admin_feature_board_index'
        ));

        return $menu;
    }
}
