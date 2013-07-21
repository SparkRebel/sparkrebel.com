<?php

namespace PW\FlagBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User,
    PW\FlagBundle\Document\Flag;

class FlagController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/flags/{type}/{reasonType}/{status}", defaults={"reasonType"="all", "status"="pending"}, name="admin_flag_index")
     * @Template
     */
    public function indexAction(Request $request, $type, $reasonType = null, $status = null)
    {
        /* @var $flagManager \PW\FlagBundle\Model\FlagManager */
        $flagManager = $this->get('pw_flag.flag_manager');
        $qb = $flagManager->getRepository()->findByTargetType($type, $reasonType, $status);

        /* @var $paginator \Knp\Component\Flagr\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        return array(
            'flagItems'  => $pagination,
            'type'       => $type,
            'reasonType' => $reasonType,
            'status'     => $status,
        );
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/flag/{type}/{id}", name="admin_flag_details")
     * @Template
     */
    public function detailsAction(Request $request, $type, $id)
    {
        /* @var $flagManager \PW\FlagBundle\Model\FlagManager */
        $flagManager = $this->get('pw_flag.flag_manager');
        $flag = $flagManager->find($id);
        if (!$flag) {
            throw $this->createNotFoundException("Flagged item not found");
        }
        return array(
            'type' => $type,
            'flag' => $flag,
        );
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/flag/{type}/approve/{id}", name="admin_flag_approve")
     * @Template
     */
    public function approveAction(Request $request, $type, $id)
    {
        /* @var $flagManager \PW\FlagBundle\Model\FlagManager */
        $flagManager = $this->get('pw_flag.flag_manager');
        $flag = $flagManager->find($id);
        if (!$flag) {
            throw $this->createNotFoundException("Flagged item not found");
        }

        $me = $this->get('security.context')->getToken()->getUser();
        $flagManager->approve($flag, $me);

        $this->get('session')->setFlash('success', "Successfully approved flagged item report");
        return $this->redirect($this->generateUrl('admin_flag_index', array(
            'type' => $type,
        )));
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/flag/{type}/reject/{id}", name="admin_flag_reject")
     * @Template
     */
    public function rejectAction(Request $request, $type, $id)
    {
        /* @var $flagManager \PW\FlagBundle\Model\FlagManager */
        $flagManager = $this->get('pw_flag.flag_manager');
        $flag = $flagManager->find($id);
        if (!$flag) {
            throw $this->createNotFoundException("Flagged item not found");
        }

        $me = $this->get('security.context')->getToken()->getUser();
        $flagManager->reject($flag, $me);

        $this->get('session')->setFlash('success', "Successfully rejected flagged item report");
        return $this->redirect($this->generateUrl('admin_flag_index', array(
            'type' => $type,
        )));
    }
}
