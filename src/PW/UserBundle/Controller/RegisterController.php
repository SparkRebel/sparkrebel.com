<?php

namespace PW\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User;

/**
 * RegisterController
 */
class RegisterController extends Controller
{
    protected $dontShowCategories = array(
        'apparel',
        'gifts & wish lists',
        'other',
        'style articles & tips',
        'jewelery',
        'accessories',
        'celeb style & red carpet',
        'fashion disasters',
        'formal',
        'inspiration & quotes',
        'outfits',
        'sales & promos',
        'shoes'

    );

    /**
     * Returns the html used by the registration process - see registration.js
     *
     * @Route("/register/welcome")
     * @Template
     */
    public function welcomeAction()
    {
        return array();
    }

    /**
     * Returns the html used by the registration process - see registration.js
     *
     * @Route("/register/templates")
     * @Template
     */
    public function templatesAction()
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');

        $brands_all = $dm->getRepository('PWUserBundle:User')
                ->createQueryBuilder()
                ->field('isActive')->equals(true)
                ->field('type')->notEqual('user')
                ->sort('usernameCanonical', 'asc')
                ->getQuery()
                ->execute();
        $brands = array();

        foreach ($brands_all as $doc) {
            $brands[$doc->getName()] = $doc;
        }
        
        $areas = $dm->getRepository('PWCategoryBundle:Area')
                ->createQueryBuilder()
                ->field('isActive')->equals(true)
                ->sort('name', 'asc')
                ->getQuery()
                ->execute();

        $this->dontShowCategories = array_map(
            function($v) {
                return new \MongoRegex("/" . $v . "/i");
            },
            $this->dontShowCategories
        );

        $categories = $dm->getRepository('PWCategoryBundle:Category')
                ->createQueryBuilder()
                ->field('type')->equals('user')
                ->field('name')->notIn($this->dontShowCategories)
                ->field('isActive')->equals(true)
                ->sort('name', 'asc')
                ->getQuery()
                ->execute();

        $user = $dm->getRepository('PWUserBundle:User')->findOneByName('Celebs');

        $celebs = $dm->getRepository('PWBoardBundle:Board')
            ->createQueryBuilder()
            ->field('createdBy')->references($user)
            ->field('isActive')->equals(true)
            ->sort('name', 'asc')
            ->getQuery()->execute();

        return compact('brands', 'areas', 'categories', 'celebs');
    }

    /**
     * Saves the registration process results and make the user follow relevant boards
     *
     * @Secure(roles="ROLE_USER")
     * @Method("POST")
     * @Route("/register/preferences")
     */
    public function preferencesAction(Request $request)
    {
        $preferences = array();
        $result = array('success' => true);


        $this->dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();

        $areas = $request->get('areas');
        $result['areas'] = $this->processAreas($areas, $me, $preferences);

        $brands = $request->get('brands');
        $result['brands'] = $this->processBrands($brands, $me, $preferences);

        $categories = $request->get('categories');
        $result['categories'] = $this->processCategories($categories, $me, $preferences);

        $celebs = $request->get('celebs');
        $result['celebs'] = $this->processCelebs($celebs, $me, $preferences);


        $me->getSettings()->setSignupPreferences($preferences);
        $this->dm->flush($me);

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Makes current user follow one of the boards at random
     * returns the selected board Id
     *
     * @param array $boards
     * @param User $user
     * @return String
     */
    protected function followRandom($boards, User $user)
    {
        $boards = $boards->toArray();
        $board  = $boards[array_rand($boards)];

        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followManager = $this->container->get('pw_user.follow_manager');
        $followManager->addFollower($user, $board);

        return $board->getId();
    }

    protected function processAreas($areas, $me, &$preferences)
    {
        $areaRepo = $this->dm->getRepository('PWCategoryBundle:Area');
        $return = array();

        foreach ($areas as $areaId) {
            $area = $areaRepo->find($areaId);
            if (!$area) {
                continue;
            }

            $boards = $area->getBoards();
            if (!$boards || $boards->count() == 0) {
                $preferences['areas'][] = array(
                    'id' => $areaId,
                    'boards' => array()
                );
                continue;
            }

            $boardId = $this->followRandom($boards, $me);
            $preferences['areas'][] = array(
                'id' => $areaId,
                'boards' => array(
                    $boardId
                )
            );
            $return[] = $boardId;
        }

        return $return;
    }

    protected function processBrands($brands, $me, &$preferences)
    {
        $followManager = $this->container->get('pw_user.follow_manager');
        $brandRepo     = $this->dm->getRepository('PWUserBundle:User');
        $return        = array();

        foreach ($brands as $brandId) {
            $brand = $brandRepo->find($brandId);
            if (!$brand) {
                continue;
            }

            $followManager->addFollower($me, $brand);
            $return[] = $brandId;
        }

        return $return;
    }

    protected function processCategories($categories, $me, &$preferences)
    {
        $categoryRepo = $this->dm->getRepository('PWCategoryBundle:Category');
        $boardRepo    = $this->dm->getRepository('PWBoardBundle:Board');
        $return       = array();

        foreach ($categories as $categoryId) {
            $category = $categoryRepo->find($categoryId);
            if (!$category) {
                continue;
            }

            $boards = $boardRepo->createQueryBuilder()
                ->field('category')->references($category)
                ->field('adminScore')->gt(0)
                ->getQuery()
                ->execute();

            if (!$boards || $boards->count() == 0) {
                $preferences['categories'][] = array(
                    'id' => $categoryId,
                    'boards' => array()
                );
                continue;
            }

            $boardId = $this->followRandom($boards, $me);
            $preferences['categories'][] = array(
                'id' => $categoryId,
                'boards' => array(
                    $boardId
                )
            );
            $return[] = $boardId;
        }

        return $return;
    }

    protected function processCelebs($celebs, $me)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $followManager = $this->get('pw_user.follow_manager');
        $boardRepo     = $dm->getRepository('PWBoardBundle:Board');
        $return        = array();

        foreach ($celebs as $celebId) {
            $board = $boardRepo->find($celebId);
            if (!$board || !$board->getCreatedBy()->isCeleb()) {
                continue;
            }
            $return[] = $celebId;
            //$return[] = $celebId;
            $followManager->addFollower($me, $board);
        }
        return $return;

    }
}
