<?php

namespace PW\FeatureBundle\Controller\Admin;

use PW\FeatureBundle\Document\Feature,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * BoardController
 *
 */
class BoardController extends Controller
{

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/feature/board", name="admin_feature_board_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $qb = $dm->createQueryBuilder('PWFeatureBundle:Feature')
            ->field('target.$ref')->equals('boards')
            ->sort('start', 'asc')
            ->sort('end', 'asc');

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        return array(
            'data' => $pagination,
        );
    }

    /**
     * @Method("POST")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/feature/board/edit", name="admin_feature_board_edit")
     * @Template
     */
    public function editAction(Request $request)
    {
        $data = $request->request->all();

        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        $feature = $dm->getRepository('PWFeatureBundle:Feature')
            ->find($data['feature']['id']);

        $feature->fromArray($data['feature']);

        $dm->persist($feature);
        $dm->flush();

        $result = array(
            'status' => 'ok',
            'data' => $feature->toArray()
        );

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Method("POST")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/feature/board/delete/{id}", name="admin_feature_board_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        $dm->createQueryBuilder('PWFeatureBundle:Feature')
            ->remove()
            ->field('_id')->equals($id)
            ->getQuery()
            ->execute();

        $result = array('status' => 'ok');
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Returns the html used by the flag process - see flag.js
     *
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/feature/board/templates")
     * @Template
     */
    public function templatesAction()
    {
        return array();
    }
}
