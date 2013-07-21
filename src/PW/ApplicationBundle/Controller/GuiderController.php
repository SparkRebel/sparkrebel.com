<?php

namespace PW\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use PW\ApplicationBundle\Response\JsonResponse;

class GuiderController extends Controller
{
    /**
     * @Method("GET")
     * @Route("/guider/seen/{id}", name="guider_seen")
     * @Cache(maxage="300")
     */
    public function seenAction(Request $request, $id)
    {
        /* @var $me \PW\UserBundle\Document\User */
        $me = $this->get('security.context')->getToken()->getUser();

        if ($me instanceOf \PW\UserBundle\Document\User) {
            /* @var $userManager \PW\UserBundle\Model\UserManager */
            $userManager = $this->get('pw_user.user_manager');
            $me->getSettings()->addViewedGuider($id);
            $userManager->update($me);
        }

        /* @var $session \Symfony\Component\HttpFoundation\Session */
        $session = $this->container->get('session');
        $guiders = $session->get('guiders', array());
        $guiders[$id] = time();
        $session->set('guiders', $guiders);

        return new JsonResponse(array('success' => true));
    }
}
