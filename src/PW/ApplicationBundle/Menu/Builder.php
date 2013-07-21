<?php

namespace PW\ApplicationBundle\Menu;

use Symfony\Component\DependencyInjection\ContainerAware;
use Knp\Menu\FactoryInterface;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory)
    {
        $me = null;
        if ($token = $this->container->get('security.context')->getToken()) {
            $me = $token->getUser();
        }
        
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->getCurrentUri());
        $menu->setChildrenAttribute('id', 'nav');

        //
        // My Stream
        $linkMyStreams = $menu->addChild('My Stream', array(
            'route' => 'home',
        ))->setAttribute('class', 'navHome');

        $linkMyStreams->addChild('All Items', array(
            'route' => 'brands_stream',
        ));

        $linkMyStreams->addChild('On Sale', array(
            'route' => 'onsale_stream',
        ));


        //// Channels
        $linkChannels = $menu->addChild('Categories', array(
            'route' => '',
        ))->setAttribute('class', 'navChannels');

        $linkChannels->addChild('All Categories', array(
            'route' => 'board_category_view_all'
        ));

        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $categories = $dm->createQueryBuilder('PWCategoryBundle:Category')
            ->field('type')->equals('user')
            ->field('isActive')->equals(true)
            ->sort('weight', 'asc')
            ->getQuery()
            ->execute();

        $categories_array_coll = new \Doctrine\Common\Collections\ArrayCollection($categories->toArray());
              
        foreach ($categories_array_coll->filter(function($e) { return $e->getIsSeparated() === false; }) as $category) {
            $linkChannels->addChild(
                $category->getName(), array(
                    'route' => 'board_category_view',
                    'routeParameters' => array('slug' => $category->getSlug())
                )
            );
        }

        
        $salesAndPromosCategory = null;
        foreach ($categories_array_coll->filter(function($e) { return $e->getIsSeparated() === true; }) as $category) {
            $linkChannels->addChild(
                $category->getName(), array(
                    'route' => 'board_category_view',
                    'routeParameters' => array('slug' => $category->getSlug())
                )
            )->setAttribute('class', 'nav-'.$category->getSlug());
            if ($category->getIsPromos()) {
                // we need Sales & Promos category for Shop menu
                $salesAndPromosCategory = $category;
            }
        }

        //
        // Spark It!
        $menu->addChild('Spark It!', array(
            'route' => 'add'
        ))->setAttribute('class', 'navAdd');

        //
        // Brands
        $linkBrandsStores = $menu->addChild('Brands', array(
            'route' => 'brands_list'
        ))->setAttribute('class', 'navBrands');
        

        //celebs
        $linkCelebs = $menu->addChild('Celebs', array(
            'route' => 'celebs',
        ))->setAttribute('class', 'navCelebs');


        //
        // Featured
        $linkFeatured = $menu->addChild('Featured', array(
            'route' => '',
        ))->setAttribute('class', 'navFeatured');

        $linkFeatured->addChild('Featured Brands', array(
            'route' => 'pw_user_featuredbrands_index'
        ));

        $linkFeatured->addChild('Featured Collections', array(
            'route' => 'pw_board_featured_index'
        ));

        $linkFeatured->addChild('Trending Collections', array(
            'route' => 'pw_board_trending_index'
        ));
        
       
        $SparkRebelUser = $dm->getRepository('PWUserBundle:User')->findOneByUsername( $this->container->getParameter('pw.system_user.sparkrebel.username') );
        if($SparkRebelUser !== null) {
            $linkFeatured->addChild('Fashion Events', array(
                'route' => 'user_profile_view',
                'routeParameters' => array('name' => $SparkRebelUser->getName())
            ));    
        }
        

        //
        // Shop
        $linkShop = $menu->addChild('Shop', array(
            'route' => ''
        ))->setAttribute('class', 'navShop');
        
        $linkShop->addChild('Shop', array(
            'route' => 'pw_store_default_index',
        ));

        if ($salesAndPromosCategory) {
            $linkShop->addChild('Sales & Promos', array(
                'route' => 'board_category_view',
                'routeParameters' => array('slug' => $category->getSlug())
            ));
        } else {
            $linkShop->addChild('Sales & Promos', array(
                'route' => 'pw_store_default_index',
            ));
        }

        return $menu;
    }

    public function footerMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->getCurrentUri());

        $menu->addChild('About', array(
            'uri' => '/about',
        ));

        $menu->addChild('Contact', array(
            'uri' => 'mailto:info@sparkrebel.com',
        ));

        $menu->addChild('FAQ', array(
            'uri' => '/faq',
        ));

        $menu->addChild('Privacy Policy', array(
            'uri' => '/privacy',
        ));

        $menu->addChild('Terms of Service', array(
            'uri' => '/terms',
        ));

        $menu->addChild('Jobs', array(
            'uri' => '/jobs',
        ));

        $menu->addChild('Sitemap', array(
            'uri' => null,
        ));

        $menu->addChild('Twitter', array(
            'uri' => 'https://twitter.com/#!/sparkrebel',
        ))->setLinkAttribute('rel', 'nofollow')
          ->setLinkAttribute('target', '_blank');

        $menu->addChild('Tweet This!', array(
            'uri' => 'http://twitter.com/?status=' . urlencode("Check this out! {$this->getCurrentUri(true)} via @SparkRebel"),
        ))->setLinkAttribute('rel', 'nofollow')
          ->setLinkAttribute('target', '_blank');

        $menu->addChild('Facebook', array(
            'uri' => 'https://www.facebook.com/SparkRebel',
        ))->setLinkAttribute('rel', 'nofollow')
          ->setLinkAttribute('target', '_blank');

        return $menu;
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getCurrentUri($absolute = false)
    {
        $request = null;
        if ($this->container->isScopeActive('request')) {
            $request = $this->container->get('request');
        }

        if ($absolute) {
            if (!$request) {
                return 'http://sparkrebel.com';
            }
            return $request->getUri();
        } else {
            if (!$request) {
                return '/';
            }
            $me = null;
            if ($token = $this->container->get('security.context')->getToken()) {
                $me = $token->getUser();
            }
            if (!is_object($me) && $request->getRequestUri() == $request->getBaseUrl() . '/') {
                return $this->container->get('router')->generate('board_category_view_all');
            } else {
                return $request->getRequestUri();
            }
        }
    }
}
