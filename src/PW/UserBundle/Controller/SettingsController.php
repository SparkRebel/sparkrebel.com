<?php

namespace PW\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use PW\UserBundle\Form\Type\User\SettingsFormType;

class SettingsController extends Controller
{
    /**
     * @Secure(roles="ROLE_USER")
     * @Method({"GET", "POST"})
     * @Route("/member/settings")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $me   = $this->container->get('security.context')->getToken()->getUser();
        $form = $this->createForm(new SettingsFormType(), $me->getSettings());

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $dm = $this->get('doctrine_mongodb.odm.document_manager');
                $dm->persist($me);
                $dm->flush(null, array('safe' => false, 'fsync' => false));

                $this->get('session')->setFlash('success', 'Settings saved successfully');
                return $this->redirect($this->generateUrl('pw_user_settings_index'));
            }
        }

        return array(
            'me'   => $me,
            'form' => $form->createView(),
        );
    }
}
