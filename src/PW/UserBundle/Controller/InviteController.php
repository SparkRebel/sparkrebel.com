<?php

namespace PW\UserBundle\Controller;

use PW\ApplicationBundle\Controller\AbstractController,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure,
    Symfony\Component\HttpFoundation\Response;

class InviteController extends AbstractController
{
    /**
     * @Method("GET")
     * @Route("/invite-friends")
     * @Template
     */
    public function friendsAction()
    {
        return array(
            'assignedCode' => $this->getCurrentUser()->getAssignedInviteCode(),
        );
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Method("GET")
     * @Route("/_fb_friends")
     * @Template
     */
    public function fbFriendsAction()
    {
        $me = $this->container->get('security.context')->getToken()->getUser();
        $facebook = $this->container->get('fos_facebook.api');

        // Make friends out of Facebook friends who are also Users
        $installedFriendsUrl = sprintf("/%s/friends?fields=installed", $me->getFacebookId());
        $installed = array();
        do {
            try {
                $friendData = $facebook->api($installedFriendsUrl);
            } catch (FacebookApiException $e) {
                $friendData = array();
            }

            foreach ($friendData as $i => $friend) {
                if (isset($friend['id']) && isset($friend['installed'])) {
                    $installed[] = $friend['id'];
                }
            }

            $installedFriendsUrl = null;
            if (isset($friendData['paging']['next'])) {
                $installedFriendsUrl = str_replace(\BaseFacebook::$DOMAIN_MAP['graph'], '', $friendData['paging']['next']);
            }

        } while (!empty($installedFriendsUrl));

        dd($installed);
    }
}
