<?php

namespace PW\AssetBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure;


class DefaultController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/assets/getty-images", name="admin_getty_images")
     * @Template
     */
    public function gettyListAction(Request $request)
    {
        $qb = $this->get('doctrine_mongodb.odm.document_manager')
            ->getRepository('PWAssetBundle:Asset')
            ->createQueryBuilder()
            ->field('fromGetty')->equals(true);



        /* @var $paginator \Knp\Component\Postr\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        return array('assets' => $pagination);
    }

}
