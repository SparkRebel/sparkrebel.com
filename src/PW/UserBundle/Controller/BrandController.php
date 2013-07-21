<?php

namespace PW\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\PostBundle\Form\Type\CommentFormType;

/**
 * BrandController
 */
class BrandController extends Controller
{
    /**
     * listAction
     *
     * @Route("/brands/all/", name="brands_list")
     * @Template
     *
     * @return array
     */
    public function listAction()
    {
        $brands = $this->getAllBrands();

        $brandsByLetter = array();
        foreach ($brands as $brand) {
            $firstLetter = strtoupper(substr($brand->getName(), 0, 1));
            if (!ctype_alpha($firstLetter)) {
                $firstLetter = '#';
            }
            $brandsByLetter[$firstLetter][] = $brand;
        }

        return array(
            'brands' => $brandsByLetter,
            'brands_i_follow' => $this->getBrandsIFollow()
        );
    }

    /**
     * Settings action
     *
     * @param Request $request object
     *
     * @Route("/brands/settings", name="brands_settings")
     * @Secure(roles="ROLE_USER")
     * @Template
     */
    public function settingsAction(Request $request)
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();

        $brands_i_follow = new \Doctrine\Common\Collections\ArrayCollection($this->getBrandsForUser($me)->toArray());
        $brands_i_follow = $brands_i_follow
            ->map(function($e) { return $e->getTarget(); })
            ->filter(function($e) { return in_array($e->getType(), array('brand', 'merchant')); });

        if ($request->isMethod('post')) {
            $brands_to_follow = $request->get('brands');
            $this->processBrands($brands_to_follow, $brands_i_follow, $me);
            $this->get('session')->setFlash('success', 'Your brand preferences have been updated.');
            return $this->redirect($this->generateUrl('brands_stream'));
        }


        

        $brands = $this->getAllBrands();
        foreach ($brands as $e) {
            if ($brands_i_follow->contains($e)) {
                $brands->removeElement($e);
            }
        }

        $iterator = $brands_i_follow->getIterator();        
        $iterator->uasort(function ($first, $second) {
            return strcmp($first->getName() , $second->getName());
        });

        return array('brands' => $brands, 'brands_i_follow' => $iterator);
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/brands/featured", name="pw_user_featuredbrands_index")
     * @Template("PWUserBundle:FeaturedBrands:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $now = new \MongoDate();
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $featured = $dm->getRepository('PWFeatureBundle:Feature');

        $brands = $featured->findBy(
            array(
                'start' => array('$lt' => $now),
                'end' => array('$gt' => $now),
                'isActive' => true,
                'target.$ref' => 'users',
            )
        )->sort(
            array(
                'priority' => 1,
                'created' => -1
            )
        );

        return array(
            'featured' => $brands
        );
    }

    /**
     * @Method("GET")
     * @Route("/brands/{brand_id}/{slug}", name="brands_stream", defaults={"brand_id" = null, "slug" = null})
     * @Template("PWUserBundle:Brand:stream.html.twig")
     */
    public function homeAction(Request $request, $brand_id)
    {
        $me = $this->get('security.context')->getToken()->getUser();

        if($me instanceof \PW\UserBundle\Document\User) {
            $brands_i_follow = new \Doctrine\Common\Collections\ArrayCollection($this->getBrandsForUser($me)->toArray());
            $brands_i_follow = $brands_i_follow
                ->map(function($e) { return $e->getTarget(); })
                ->filter(function($e) { return in_array($e->getType(), array('brand', 'merchant')); });
        } else {
            $brands_i_follow = new \Doctrine\Common\Collections\ArrayCollection;
        }

        $iterator = $brands_i_follow->getIterator();        
        $iterator->uasort(function ($first, $second) {
            return strcmp($first->getName() , $second->getName());
        });

        return array(
            'title' => is_object($me) ? 'Brands I Follow' : 'Sparks From Brands',
            'brands_i_follow' => $iterator, 'brand_id' => $brand_id
        );
    }

    /**
     * @Method("GET")
     * @Route("/onsale/", name="onsale_stream")
     * @Template("PWUserBundle:OnSale:stream.html.twig")
     */
    public function onsaleAction()
    {
        $me    = $this->get('security.context')->getToken()->getUser();
        $title = is_object($me) ? 'On Sale' : 'Sparks From Brands';
        return array('title' => $title);
    }

    protected function brandHasSaleItems($user)
    {
        $redis = $this->get('snc_redis.default');
        return $redis->exists('stream:{' . $user->getId() . '}:brandOnsale');
    }

    /**
     * View action
     *
     * @param Request $request object
     * @param string  $name    user name, spaces 'n all
     * @param string  $section which section to render
     *
     * @Route("/brand/{slug}/{section}.{_format}", defaults={"_format" = "html", "section" = "myBoards"}, requirements={"slug" = "[^/]+", "_format" = "html|json"})
     * @Template
     */
    public function viewAction(Request $request, $slug, $section = 'myBoards')
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $user = $dm->getRepository('PWUserBundle:User')->findOneByUsername($slug);
        $form = $this->createForm(new CommentFormType(false));

        if (!$user) {
            throw $this->createNotFoundException("Brand not found");
        } elseif ($user->getDeleted()) {
            throw $this->createNotFoundException("Brand not found");
        }

        $type = $user->getUserType();
        if ($type === 'user') {
            return $this->redirect($this->generateUrl('user_profile_view', array('slug' => $user->getName())), 301);
        }

        $userManager = $this->get('pw_user.user_manager');

        $return = compact('user', 'section');
        $functionName = 'getViewData' . ucfirst($section);
        $userManager->$functionName($return);

        $return['form'] = $form->createView();
        $return['formInstance'] = $form;
        $return['hasSaleItems'] = true;//$this->brandHasSaleItems($user);

        if ($request->isXmlHttpRequest()) {
            if ($section === 'myBoards') {
                return $this->render("PWUserBundle:Brand:partials/$section.html.twig", $return);
            }
            return $this->render("PWUserBundle:Profile:partials/$section.html.twig", $return);
        }

        $userManager->getViewDataCommon($return);
        $userManager->getViewDataPeopleIFollow($return);
        return $return;
    }

    protected function getBrandsForUser(\PW\UserBundle\Document\User $user)
    {
        return $this->get('pw_user.follow_manager')->getRepository()->findFollowingByUser($user, 'users')
            ->getQuery()
            ->execute();
    }

    protected function processBrands($brands, \Doctrine\Common\Collections\ArrayCollection $brands_i_follow, \PW\UserBundle\Document\User $me)
    {
        $dm            = $this->get('doctrine_mongodb.odm.document_manager');
        $followManager = $this->get('pw_user.follow_manager');
        $userRepo      = $dm->getRepository('PWUserBundle:User');
        $return        = array();

        $currently_followed_ids = $brands_i_follow->map(function($e) { return $e->getId();});

        if($brands === null) {
            $brands = array();
        }

        if ($currently_followed_ids === null) {
            $currently_followed_ids = array();
        }

        foreach ($brands as $brandId) {
            $brand = $userRepo->find($brandId);
            if (!$brand || !in_array($brand->getType(), array('brand', 'merchant'))) {
                continue;
            }
            $followManager->addFollower($me, $brand);
        }

        foreach ($currently_followed_ids as $id) {
            if (in_array($id, $brands) === false) {
                $brand_to_unfollow = $userRepo->find($id);
                $followManager->removeFollower($me, $brand_to_unfollow);
            }
        }

    }

    protected function getAllBrands()
    {
        $brands = new \Doctrine\Common\Collections\ArrayCollection;
        $return = new \Doctrine\Common\Collections\ArrayCollection($this->get('doctrine_mongodb.odm.document_manager')
                    ->createQueryBuilder('PWUserBundle:User')
                    ->field('type')->in(array('brand', 'merchant'))
                    ->field('isActive')->equals(true)
                    ->sort('name', 'asc')                                        
                    ->getQuery()->execute()->toArray()
            );

        foreach ($return as $doc) {
            $brands[$doc->getName()] = $doc;
        }

        return $brands;
    }

    protected function hydrateResults($docType, $results)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $repo = $dm->getRepository($docType);

        $arr = array();
        foreach ($results as $id => $cnt) {
            $item = $repo->find($id);

            if ($item) {
                $arr[] = array(
                    'item' => $item,
                    'count' => $cnt,
                );
            }
        }

        return $arr;
    }

    protected function getBrandsIFollow()
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();

        if (is_object($me) !== true) {
            return array();
        }
        
        $brands_i_follow = new \Doctrine\Common\Collections\ArrayCollection($this->getBrandsForUser($me)->toArray());
        $brands_i_follow = $brands_i_follow
            ->map(function($e) { return $e->getTarget(); })
            ->filter(function($e) { return in_array($e->getType(), array('brand', 'merchant')); });


        $iterator = $brands_i_follow->getIterator();        
        $iterator->uasort(function ($first, $second) {
            return strcmp($first->getName() , $second->getName());
        });
        return $iterator;
    }

    /**
     * @Template
     */
    public function trendingFollowersAction($tracking)
    {
        /**
         * workaround for twig:render in template
         */
        $this->get('router')->getContext()->setHost($this->container->getParameter('host'));

        $followManager = $this->get('pw_user.follow_manager');
        $ids = $followManager->getRepository()
            ->findRecentBrandFollowers(6, 14);

        $hydrated = $this->hydrateResults('PWUserBundle:User', $ids);

        return array(
            'trending' => $hydrated,
            'tracking' => $tracking
        );
    }
}
