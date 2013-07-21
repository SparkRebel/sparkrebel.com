<?php

namespace PW\ActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure;

class ActivityController extends Controller
{
    /**
     * List all activity for current User
     *
     * @Method("GET")
     * @Secure(roles="ROLE_USER")
     * @Route("/activity/user")
     *
     * @return array
     */
    public function myUserAction()
    {
        $me = $this->get('security.context')->getToken()->getUser();
        return $this->forward('PWActivityBundle:Activity:user', array('name' => $me->getName()));
    }

    /**
     * List all activity for a user
     *
     * @Method("GET")
     * @Route("/activity/user/{name}")
     * @Template
     */
    public function userAction(Request $request, $name)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $me = $this->container->get('security.context')->getToken()->getUser();
        $user = $dm->getRepository('PWUserBundle:User')->findOneByName($name);

        $isMe = false;
        if ($me instanceof \PW\UserBundle\Document\User) {
            $isMe = ($user->getId() === $me->getId());
        }

        if (!$user) {
            throw $this->createNotFoundException("User with name '{$name}' not found");
        } elseif ($user->getDeleted()) {
            throw $this->createNotFoundException("User has been removed");
        }

        $qb = $dm->createQueryBuilder('PWActivityBundle:Activity')
            ->field('user')->references($user)
            ->field('category')->equals('user')
            ->sort('created', -1);

        return array(
            'activities' => $this->get('knp_paginator')->paginate($qb,
                $request->query->get('page', 1),
                $request->query->get('pagesize', 15)
            ),
            'friend' => !$isMe
        );
    }
}
