<?php

namespace PW\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User,
    PW\PostBundle\Form\Type\CommentFormType,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * ProfileController
 */
class ProfileController extends Controller
{
    /**
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Route("/member/profile", name="user_profile_view_self")
     * @Template
     */
    public function viewUserAction()
    {
        $me = $this->get('security.context')->getToken()->getUser();
        return $this->redirect($this->generateUrl('user_profile_view', array(
            'name'    => $me->getName(),
            'section' => 'myBoards'
        )));
    }


    /**
     * @Route("/member/profile/{name}/{section}", defaults={"section"="myBoards"}, name="user_profile_view")
     * @Template
     */
    public function viewAction(Request $request, $name, $section)
    {
        if($name === 'Celebs') {
             return $this->redirect($this->generateUrl('celebs'), 301);
        }

        $dm   = $this->container->get('doctrine_mongodb.odm.document_manager');
        $user = $dm->getRepository('PWUserBundle:User')->findOneByName($name);
        $form = $this->createForm(new CommentFormType(false));


        if (!$user) {
            throw $this->createNotFoundException("User with name '{$name}' not found");
        } elseif ($user->getDeleted()) {
            throw $this->createNotFoundException("User has been removed");
        }

        $type = $user->getUserType();
        if ($type === 'brand' || $type === 'merchant') {
            return $this->redirect($this->generateUrl('pw_user_brand_view', array('slug' => $user->getUsername())), 301);
        }

        $userManager = $this->get('pw_user.user_manager');

        $params = array();
        if ($request->query->get('page')) {
            $params['page'] = (int) $request->query->get('page');
        }
        $follow_type = $request->query->get('follow_type') ? $request->query->get('follow_type') : null;

        if(!in_array($follow_type, array('user', 'brand', 'celeb')))
          $follow_type = 'user';

        $params['follow_type'] = $follow_type;
        $return = compact('user', 'section');
        $functionName = 'getViewData' . ucfirst($section);
        $userManager->$functionName($return, $params);

        if($section !== 'peopleIFollow') {
           $userManager->getViewDataPeopleIFollow($return, array());
        }

        $return['form'] = $form->createView();
        $return['formInstance'] = $form;

        if ($request->isXmlHttpRequest()) {
            return $this->render("PWUserBundle:Profile:partials/$section.html.twig", $return);
        }

        $userManager->getViewDataCommon($return);

        return $return;
    }


}
