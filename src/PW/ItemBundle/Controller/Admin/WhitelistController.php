<?php

namespace PW\ItemBundle\Controller\Admin;

use PW\ItemBundle\Document\Whitelist,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * WhitelistController
 */
class WhitelistController extends Controller
{

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/whitelist", name="admin_whitelist_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $qb = $dm->createQueryBuilder('PWItemBundle:Whitelist')
            ->sort('type', 'asc')
            ->sort('id', 'asc');

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
     * @Route("/admin/whitelist/edit", name="admin_whitelist_edit")
     * @Template
     */
    public function editAction(Request $request)
    {
        $data = $request->request->all();

        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        if ($data['whitelistId']) {
            $dm->createQueryBuilder('PWItemBundle:Whitelist')
                ->remove()
                ->field('id')->equals($data['whitelistId'])
                ->getQuery()
                ->execute();
        }

        $row = new Whitelist();
        $row->setId($data['whitelistName']);
        $row->setType($data['whitelistType']);

        $dm->persist($row);
        $dm->flush();

        $result = array('status' => 'ok');

        $result['data'] = array(
            'id' => $row->getId()
        );
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Method("DELETE")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/whitelist/delete/{id}", name="admin_whitelist_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        $dm->createQueryBuilder('PWItemBundle:Whitelist')
            ->remove()
            ->field('id')->equals($id)
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
     * @Route("/admin/whitelist/templates")
     * @Template
     */
    public function templatesAction()
    {
        return array();
    }
}
