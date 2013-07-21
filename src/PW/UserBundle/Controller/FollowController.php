<?php

namespace PW\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache,
    JMS\SecurityExtraBundle\Annotation\Secure;
use PW\ApplicationBundle\Response\JsonResponse;
use PW\UserBundle\Document\User;
use PW\BoardBundle\Document\Board;

class FollowController extends Controller
{
    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/follow/user/{name}")
     */
    public function userAction(Request $request, $name)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->get('pw_user.user_manager');
        $user = $userManager->getRepository()->findOneByName($name);

        if (!$user) {
            throw $this->createNotFoundException("User with name '{$name}' not found.");
        }

        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followManager = $this->container->get('pw_user.follow_manager');

        $me = $this->get('security.context')->getToken()->getUser();
        $follow = $followManager->addFollower($me, $user);
        $followManager->update($follow);
        
        
        //cache user and his boards in session        
        
        if ($request->isXmlHttpRequest()) {
            $response = new Response(json_encode(array(
                'result' => 'ok',
                'data'   => array(
                    'id'   => $user->getId(),
                    'type' => $user->getUserType(),
                )
            )));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $this->get('session')->setFlash('success', sprintf("You are now following user '%s'.", $user->getName()));
        return $this->redirect($this->generateUrl('user_profile_view', array('name' => $user->getName())));
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/unfollow/user/{name}", name="pw_user_unfollow_user")
     */
    public function unuserAction(Request $request, $name)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->get('pw_user.user_manager');
        $user = $userManager->getRepository()->findOneByName($name);

        if (!$user) {
            throw $this->createNotFoundException("User with name '{$name}' not found.");
        }

        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followManager = $this->container->get('pw_user.follow_manager');
        $me = $this->get('security.context')->getToken()->getUser();
        $followManager->removeFollower($me, $user);
                
        if ($request->isXmlHttpRequest()) {
            $response = new Response(json_encode(array(
                'result' => 'ok',
                'data'   => array(
                    'id'   => $user->getId(),
                    'type' => $user->getUserType(),
                )
            )));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $this->get('session')->setFlash('success', sprintf("You successfully unfollowed user '%s'.", $user->getName()));
        return $this->redirect($this->generateUrl('user_profile_view', array('name' => $user->getName())));
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/follow/collection/{id}")
     */
    public function boardAction(Request $request, $id)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->get('pw_board.board_manager');
        $board = $boardManager->find($id);

        if (!$board) {
            throw $this->createNotFoundException("Collection not found");
        }

        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followManager = $this->container->get('pw_user.follow_manager');

        $me = $this->get('security.context')->getToken()->getUser();
        $follow = $followManager->addFollower($me, $board);
        $followManager->update($follow);
        
        
        if ($request->isXmlHttpRequest()) {
            $response = new Response(json_encode(array(
                'result' => 'ok',
                'data'   => array(
                    'id'   => $board->getId(),
                    'type' => 'board',
                )
            )));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $this->get('session')->setFlash('success', sprintf("You are now following collection '%s'.", $board->getName()));
        return $this->redirect($this->generateUrl('pw_board_default_view', array('name' => $board->getId())));
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/unfollow/collection/{id}", name="pw_user_unfollow_board")
     */
    public function unboardAction(Request $request, $id)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->get('pw_board.board_manager');
        $board = $boardManager->find($id);

        if (!$board) {
            throw $this->createNotFoundException("Collection not found");
        }

        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followManager = $this->container->get('pw_user.follow_manager');
        $me = $this->get('security.context')->getToken()->getUser();
        $followManager->removeFollower($me, $board);
        
        
        if ($request->isXmlHttpRequest()) {
            $response = new Response(json_encode(array(
                'result' => 'ok',
                'data'   => array(
                    'id'   => $board->getId(),
                    'type' => 'board',
                )
            )));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $this->get('session')->setFlash('success', sprintf("You successfully unfollowed collection '%s'.", $board->getName()));
        return $this->redirect($this->generateUrl('pw_board_default_view', array('name' => $board->getId())));
    }

    /**
     * Called by the frontend js to change follow to unfollow
     * Returns an object of which users and boards the loggerd in user is following, indexed by type
     *
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Route("/member/following/{id}")
     * @Cache(maxage="600")
     */
    public function followingAction($id = null)
    {
        $result = array();
        $me = $this->get('security.context')->getToken()->getUser();

        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followManager = $this->container->get('pw_user.follow_manager');

        $following = $followManager->getRepository()
            ->findFollowingByUser($me)
            ->eagerCursor(true)
            ->getQuery()->execute();
        
        
        foreach ($following as $follow) {
            if ($target = $follow->getTarget()) {
                if ($target instanceOf Board) {                    
                    $result['board'][] = $target->getId();
                    continue;
                }
                if ($target instanceOf User) {                    
                    $result[$target->getUserType()][] = $target->getId();
                    continue;
                }
            }
        }               
        
        $response = new Response(json_encode($result));  
        return $response;
        //return new JsonResponse($result);
    }

    /**
     * Called by the frontend js for the bottom bar
     * Returns an object of friends
     *
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Route("/member/friends/{id}")
     * @Cache(maxage="600")
     */
    public function friendsAction($id = null)
    {
        $result = array();
        $me = $this->get('security.context')->getToken()->getUser();
        if ($me instanceOf User) {
            /* @var $followManager \PW\UserBundle\Model\FollowManager */
            $followManager = $this->container->get('pw_user.follow_manager');

            $friends = $followManager->getRepository()
                ->findFriendsByUser($me)
                ->eagerCursor(true)
                ->getQuery()->execute();

            foreach ($friends as $follow) {
                if ($friend = $follow->getTarget()) {
                    $result[$friend->getId()] = array(
                        'id'   => $friend->getId(),
                        'name' => $friend->getName(),
                        'icon' => $friend->getIcon() ? $friend->getIcon()->getUrl() : false,
                    );
                }
            }

            ksort($result);
            $result = array_values($result);
        }

        return new JsonResponse($result);
    }
    
    
}
