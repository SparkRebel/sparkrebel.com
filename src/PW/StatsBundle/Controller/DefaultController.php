<?php

namespace PW\StatsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }

    /** @Route("/track/stats/share/{id}", name="pw_stat_share") @Method("POST") */
    public function shareAction($id)
    {
        $postManager = $this->get('pw_post.post_manager');
        $post = $postManager->find($id);

        if (!$post) {
            throw $this->createNotFoundException("Spark not found");
        } elseif ($post->getDeleted()) {
            throw $this->createNotFoundException("Spark has been removed");
        }

        if ($post->getImage()->isGetty()) {
            if (!$this->get('pw.stats')->isHttpUserAgentBot()) {
                $this->get('pw.stats')
                    ->record('shared', $post->getImage(), $me, $this->get('request')->getClientIp());
            }
        }
        return new Response("");
    }
}
