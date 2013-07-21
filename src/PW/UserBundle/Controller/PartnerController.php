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
    PW\UserBundle\Form\Type\PartnerRegistrationFormType,
    PW\UserBundle\Document\User,
    PW\UserBundle\Document\Partner;

class PartnerController extends Controller
{
    /**
     * @Method("GET")
     * @Route("/partners", name="user_partner_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $partner = new Partner();
        $form    = $this->_getRegistrationForm($partner);

        // Check for current request
        $me = $this->get('security.context')->getToken()->getUser();
        $userTotal = 0;
        if ($me instanceOf User) {
            $dm = $this->get('doctrine_mongodb.odm.document_manager');
            $userTotal = $dm->getRepository('PWUserBundle:Partner')->getTotalByUser($me);
        }

        return array(
            'form'      => $form->createView(),
            'userTotal' => $userTotal,
        );
    }

    /**
     * @Route("/partner/register", name="user_partner_register")
     * @Template
     */
    public function registerAction(Request $request)
    {
        $partner = new Partner();
        $form    = $this->_getRegistrationForm($partner);

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                /* @var $asset \PW\AssetBundle\Document\Asset */
                $asset = $this->get('pw.asset')->addUpload($request, 'user_partner_registration', 'icon');
                if ($asset instanceOf \PW\AssetBundle\Document\Asset) {
                    $partner->setIcon($asset);
                }
                $dm = $this->get('doctrine_mongodb.odm.document_manager');
                $dm->persist($partner);
                $dm->flush(null, array('safe' => false, 'fsync' => false));
                $this->get('session')->setFlash('success', 'Your request to be a partner has been received and will be reviewed soon.');
                return $this->redirect($this->generateUrl('user_partner_index'));
            }
        }

        // Check for current request
        $me = $this->get('security.context')->getToken()->getUser();
        $userTotal = 0;
        if ($me instanceOf User) {
            $dm = $this->get('doctrine_mongodb.odm.document_manager');
            $userTotal = $dm->getRepository('PWUserBundle:Partner')->getTotalByUser($me);
        }

        return array(
            'form'      => $form->createView(),
            'userTotal' => $userTotal,
        );
    }

    /**
     * @Route("/partner/learn", name="user_partner_learn")
     * @Template
     */
    public function learnAction()
    {
        return array();
    }

    /**
     * @Route("/partner/login", name="user_partner_login")
     * @Template("PWUserBundle:Security:login.html.twig")
     */
    public function loginAction(Request $request)
    {
        $me = $this->get('security.context')->getToken()->getUser();
        if ($me instanceOf User) {
            return $this->redirect($this->generateUrl('home'));
        }

        /* @var $session \Symfony\Component\HttpFoundation\Session */
        $session = $request->getSession();

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            $error = $error->getMessage();
        }

        // Last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $result = array(
            'partner'       => true,
            'last_username' => $lastUsername,
            'error'         => $error,
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('PWUserBundle:Security:partials/loginFormUsername.html.twig', $result);
        } else {
            return $result;
        }
    }

    protected function _getRegistrationForm(Partner $partner)
    {
        $me = $this->get('security.context')->getToken()->getUser();
        if ($me instanceOf User) {
            //TODO: fix this, never worked
            //$partner->setCreatedBy($me);
            //$partner->setFromUser($me);
        }
        return $this->createForm(new PartnerRegistrationFormType(), $partner);
    }
}
