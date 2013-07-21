<?php

namespace PW\BoardBundle\Controller;

use PW\BoardBundle\Document\Board,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class TrendingController extends Controller
{
    protected function hydrateResults($docType, $results)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $repo = $dm->getRepository($docType);

        $arr = array();
        foreach ($results as $id => $cnt) {
            $item = $repo->find($id);

            if ($item) {
                $arr[] = array(
                    'item' => $item,
                    'count' => $cnt,
                );
            }
        }

        return $arr;
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/collections/trending")
     * @Template
     * @Cache(expires="+1 hour")
    */
    public function indexAction(Request $request)
    {
        $trending = $this->get('doctrine_mongodb.odm.document_manager')->getRepository('PWPostBundle:Post')->findRecentTrendingFollows(12,14);

        $hydrated = $this->hydrateResults('PWBoardBundle:Board', $trending);

        return array(
            'trending' => $hydrated,
        );
    }

    /**
     * @Template
     */
    public function trendingCollectionsAction($tracking)
    {
        /**
         * workaround for twig:render in template
         */
        $this->get('router')->getContext()->setHost($this->container->getParameter('host'));

        $trending = $this->get('doctrine_mongodb.odm.document_manager')->getRepository('PWPostBundle:Post')->findRecentTrendingFollows(4,14);

        $hydrated = $this->hydrateResults('PWBoardBundle:Board', $trending);

        return array(
            'trending' => $hydrated,
            'tracking' => $tracking
        );
    }
}
