<?php

namespace PW\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use PW\UserBundle\Document\User;

class SecurityController extends BaseController
{
    public function loginAction()
    {
        $me = $this->container->get('security.context')->getToken()->getUser();
        if (is_object($me) && $me instanceOf User) {
            return new RedirectResponse($this->container->get('router')->generate('home'), 302);
        }

        /** @var \Symfony\Component\HttpFoundation\Request $request  */
        $request = $this->container->get('request');
        /** @var \Symfony\Component\HttpFoundation\Session $session  */
        $session = $this->container->get('session');

        $subId = $session->get('sub_id');
        if (empty($subId) && $request->query->has('sub_id')) {
            $subId = $request->query->get('subid');
            $session->set('sub_id', $subId);
        }

        // @see https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/blob/master/Resources/doc/a_note_about_security.md
        if ($session->has('_security.target_path')) {
            if (false !== strpos($session->get('_security.target_path'), $this->generateUrl('fos_oauth_server_authorize'))) {
                $session->set('_fos_oauth_server.ensure_logout', true);
            }
        }

        return parent::loginAction();
    }

    /**
     * @Route("/fb_login_check", name="fb_login_check")
     */
    public function facebookSecurityCheckAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="fb_logout")
     */
    public function facebookSecurityLogoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}
