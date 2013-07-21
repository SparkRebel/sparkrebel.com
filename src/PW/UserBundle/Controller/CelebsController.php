<?php

namespace PW\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User;

/**
 * CelebsController
 */
class CelebsController extends Controller
{

    /**
     * Index action
     *
     * @param Request $request object
     *
     * @Route("/celebs", name="celebs")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $user = $dm->getRepository('PWUserBundle:User')->findOneByName('Celebs');


        if (!$user) {
            throw $this->createNotFoundException("Celebs user not found");
        }

      	$boards = $this->getBoards($user, $dm);

      	$celebsByLetter = array();
        foreach ($boards as $celeb) {
            $firstLetter = strtoupper(substr($celeb->getName(), 0, 1));
            if (!ctype_alpha($firstLetter)) {
                $firstLetter = '#';
            }
            $celebsByLetter[$firstLetter][] = $celeb;
        }        
        return array('celebs' => $celebsByLetter, 'celebs_i_follow' => $this->getCelebsIFollow());
    }

    /**
     * Index action
     *
     * @param Request $request object
     *
     * @Route("/celebs/my/{celeb_id}", name="my_celebs", defaults={"celeb_id" = null})
     * @Secure(roles="ROLE_USER")
     * @Template
     */
    public function myCelebsAction(Request $request, $celeb_id = null)
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();
        $followRepo = $dm->getRepository('PWUserBundle:Follow');

        $celebs_i_follow = $followRepo->getCelebsThatUserFollows($me);
        $iterator = $celebs_i_follow->getIterator();        
        $iterator->uasort(function ($first, $second) {
            return strcmp($first->getName() , $second->getName());
        });
        
        return array(
            'celebs_i_follow' => $iterator,
            'celeb_id' => $celeb_id
        );

    }

    /**
     * Settings action
     *
     * @param Request $request object
     *
     * @Route("/celebs/settings", name="celebs_settings")
     * @Template
     */

    public function settingsAction(Request $request)
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();
        $user = $dm->getRepository('PWUserBundle:User')->findOneByName('Celebs');

        if (!$user) {
            throw $this->createNotFoundException("Celebs user not found");
        }

        if (false === $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('celebs'));
        }

        if($request->isMethod('post')) {
            $celebs_to_follow = $request->get('celebs');
            //$this->get('pw_user.follow_manager')->clearCelebsFollowsForUser($me, $celebs_to_follow);
            $this->processCelebs($celebs_to_follow, $me);
            $this->get('session')->setFlash('success', 'Your celebs preferences have been updated.');
            return $this->redirect($this->generateUrl('my_celebs'));
        }
       

        return array('celebs_i_follow' => $this->getCelebsIFollow(), 'celebs' => $boards);

    }

    protected function processCelebs($celebs, $me)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $followManager = $this->get('pw_user.follow_manager');
        $boardRepo     = $dm->getRepository('PWBoardBundle:Board');
        $return        = array();


        $celebs_i_follow = new \Doctrine\Common\Collections\ArrayCollection($followManager->getRepository()->getCelebsThatUserFollows($me)->toArray());

        if($celebs === null) {
            $celebs = array();
        }

        foreach ($celebs as $celebId) {
            $board = $boardRepo->find($celebId);
            if (!$board || !$board->getCreatedBy()->isCeleb()) {
                continue;
            }

            if($celebs_i_follow->contains($board) === false) {
                $followManager->addFollower($me, $board);
            }

        }

        foreach($celebs_i_follow->map(function($i) {return $i->getId();}) as $id) {
            if(in_array($id, $celebs) === false) {

                $board_to_unfolllow = $boardRepo->find($id);
                $followManager->removeFollower($me, $board_to_unfolllow, false);
            }
        }
        $followManager->getDocumentManager()->flush();


    }

    protected function getBoards(User $user, $dm)
    {
    	$boardRepo = $dm->getRepository('PWBoardBundle:Board');

        return $boardRepo
            ->createQueryBuilder()
            ->field('createdBy')->references($user)
            ->field('isActive')->equals(true)
            ->sort('name', 'asc')
            ->getQuery()->execute();
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


    protected function getCelebsIFollow()
    {        
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();
        $user = $dm->getRepository('PWUserBundle:User')->findOneByName('Celebs');
        
        if (is_object($me) !== true) {
            return array();
        }

        $boards = $this->getBoards($user, $dm);
        $boards = new \Doctrine\Common\Collections\ArrayCollection($boards->toArray());

        $userManager = $this->get('pw_user.user_manager');

        $followRepo = $dm->getRepository('PWUserBundle:Follow');
        $celebs_i_follow = $followRepo->getCelebsThatUserFollows($me);

        foreach($boards as $e) {
            if($celebs_i_follow->contains($e)) {
                $boards->removeElement($e);
            }
        }
        $iterator = $celebs_i_follow->getIterator();        
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
            ->findRecentCelebsFollowers(6, 14);

        $hydrated = $this->hydrateResults('PWBoardBundle:Board', $ids);

        return array(
            'trending' => $hydrated,
            'tracking' => $tracking
        );
    }

}
