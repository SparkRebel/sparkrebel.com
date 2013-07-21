<?php

namespace PW\NewsletterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class NewsletterController extends Controller
{
    /**
     * @Route("/newsletter/{code}",name="newsletter_view")
     * @Template()
     */
    public function viewAction($code)
    {
        $log = $this->get('pw_newsletter.newsletter_email_manager')->getRepository()->findOneByCode($code);

        if(!$log) {
            return $this->redirect($this->generateUrl('home'));
        }

        return array('log' => $log);
    }
}
