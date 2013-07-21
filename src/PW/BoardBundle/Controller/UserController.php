<?php

namespace PW\BoardBundle\Controller;

use PW\BoardBundle\Document\Board,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure;

class UserController extends Controller
{
    /**
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Route("/member/collections")
     * @Template
     */
    public function boardsUserAction(Request $request)
    {
        $me = $this->get('security.context')->getToken()->getUser();
        return $this->forward('PWBoardBundle:User:boards', array(
            'id' => $me->getId(),
        ));
    }

    /**
     * @Method("GET")
     * @Route("/member/{id}/collections/{_format}", defaults={"_format"="html"}, requirements={"id"="[\da-f]{24}", "_format"="html|json"})
     * @Template
     */
    public function boardsAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();

        /* @var $user \PW\UserBundle\Document\User */
        $user = $dm->getRepository('PWUserBundle:User')->find($id);
        if (!$user) {
            throw $this->createNotFoundException('The user does not exist');
        }

        $boards = $dm->getRepository('PWBoardBundle:Board')
            ->findByUser($user)
            ->getQuery()->execute();

        return array(
            'me'        => $me,
            'boards'    => $boards,
            'user'      => $user,
            'ownedByMe' => $user->getId() == $me->getId(),
        );
    }
}
