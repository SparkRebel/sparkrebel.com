<?php

namespace PW\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure;

class DefaultController extends Controller
{
    /**
     * @Method("GET")
     * @Route("/users/{id}.{_format}", defaults={"_format" = "html"}, requirements={"id" = "[\da-f]{24}", "_format" = "html|json"})
     * @Template
     */
    public function viewAction($id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $userRepo = $dm->getRepository('PWUserBundle:User');
        $data = $userRepo->find($id);

        return array('data' => $data);
    }

    /**
     * @Method("GET")
     * @Route("/tutorial")
     * @Template
     */
    public function tutorialAction()
    {
        /* @var $me \PW\UserBundle\Document\User */
        $me = $this->get('security.context')->getToken()->getUser();

        if ($me instanceOf \PW\UserBundle\Document\User) {
            /* @var $userManager \PW\UserBundle\Model\UserManager */
            $userManager = $this->get('pw_user.user_manager');
            $me->getSettings()->setViewedTutorial(new \DateTime());
            $userManager->update($me);
        }

        /* @var $session \Symfony\Component\HttpFoundation\Session */
        $session = $this->container->get('session');
        $session->set('viewed_tutorial', true);

        return array();
    }
}
