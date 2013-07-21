<?php

namespace PW\ItemBundle\Controller\Admin;

use PW\ItemBundle\Document\Alias,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * AliasController
 */
class AliasController extends Controller
{

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/alias", name="admin_alias_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $qb = $dm->createQueryBuilder('PWItemBundle:Alias');

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
     * @Route("/admin/alias/edit", name="admin_alias_edit")
     * @Template
     */
    public function editAction(Request $request)
    {
        $data = $request->request->all();

        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        if ($data['aliasId']) {
            $dm->createQueryBuilder('PWItemBundle:Alias')
                ->remove()
                ->field('id')->equals($data['aliasId'])
                ->getQuery()
                ->execute();
        }

        $row = new Alias();
        $row->setId($data['aliasName']);
        $aliases = trim($data['aliasAliases'], ',');
        $aliases = explode(',', $aliases);
        $aliases = array_map('trim', $aliases);
        $aliases = array_filter($aliases);
        $row->setSynonyms($aliases);

        $dm->persist($row);
        $dm->flush();

        $result = array('status' => 'ok');

        $result['data'] = array(
            'id' => $row->getId(),
            'name' => $row->getId(),
            'aliases' => implode($row->getSynonyms(), ', '),
        );
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/alias/delete/{id}", name="admin_alias_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        $dm->createQueryBuilder('PWItemBundle:Alias')
            ->remove()
            ->field('id')->equals($id)
            ->getQuery()
            ->execute();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Returns the html used by the flag process - see flag.js
     *
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/alias/templates")
     * @Template
     */
    public function templatesAction()
    {
        return array();
    }
}
