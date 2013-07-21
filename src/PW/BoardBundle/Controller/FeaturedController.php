<?php

namespace PW\BoardBundle\Controller;

use PW\BoardBundle\Document\Board,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FeaturedController extends Controller
{

    /**
     * @Method({"GET", "POST"})
     * @Route("/collections/featured")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $now = new \MongoDate();
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $featured = $dm->getRepository('PWFeatureBundle:Feature');

        $boards = $featured->findBy(
            array(
                'start' => array('$lt' => $now),
                'end' => array('$gt' => $now),
                'isActive' => true,
                'target.$ref' => 'boards',
            )
        )->sort(
            array(
                'priority' => 1,
                'created' => -1
            )
        );

        return array(
            'featured' => $boards
        );
    }

}
