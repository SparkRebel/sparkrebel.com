<?php

namespace PW\InviteBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User,
    PW\InviteBundle\Document\Code,
    PW\InviteBundle\Form\Model\CreateCode,
    PW\InviteBundle\Form\Type\CreateCodeType;

class CodeController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/invite/codes/{status}", defaults={"status"="unused"}, name="admin_invite_code_index")
     * @Template
     */
    public function indexAction(Request $request, $status)
    {
        /* @var $codeManager \PW\InviteBundle\Model\CodeManager */
        $codeManager = $this->get('pw_invite.code_manager');
        $qb = $codeManager->getRepository()->findByStatus($status);

        /* @var $paginator \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        $form = $this->createFormBuilder()
            ->add('count', 'integer', array(
                'required'   => true,
                'data'       => 10,
            ))
            ->add('maxUses', 'integer', array(
                'required'   => true,
                'data'       => 0,
            ))
            ->getForm();

        return array(
            'codes'  => $pagination,
            'status' => $status,
            'form'   => $form->createView(),
        );
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/invite/code/new", name="admin_invite_code_new")
     * @Template
     */
    public function newAction(Request $request)
    {
        /* @var $codeManager \PW\InviteBundle\Model\CodeManager */
        $codeManager = $this->get('pw_invite.code_manager');

        $form = $this->createForm(
            new CreateCodeType(),
            new CreateCode()
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $me = $this->get('security.context')->getToken()->getUser();
                $formData = $form->getData();
                $code = $formData->getCode();
                $code->setCreatedBy($me);
                $code->setType('custom');
                if ($code->getMaxUses()) {
                    $code->setUsesLeft($code->getMaxUses());
                }
                $code = $codeManager->update($code);
                $this->get('session')->setFlash('success', sprintf("Successfully created invite code '%s'", $code->getValue()));
                return $this->redirect($this->generateUrl('admin_invite_code_index'));
            } else {
                $this->get('session')->setFlash('error', "There was a problem with the information you entered.");
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/invite/code/details/{id}", name="admin_invite_code_details")
     * @Template
     */
    public function detailsAction(Request $request, $id)
    {
        /* @var $codeManager \PW\InviteBundle\Model\CodeManager */
        $codeManager = $this->get('pw_invite.code_manager');
        $code = $codeManager->find($id);
        if (!$code) {
            throw $this->createNotFoundException("Invite Code not found.");
        }
        return array('code' => $code);
    }

    /**
     * @Method("POST")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/invite/codes/generate/{status}", defaults={"status"="unused"}, name="admin_invite_code_generate")
     * @Template
     */
    public function generateAction(Request $request, $status)
    {
        $form = $this->createFormBuilder()
            ->add('count', 'integer', array(
                'required'   => true,
                'data'       => 10,
            ))
            ->add('maxUses', 'integer', array(
                'required'   => true,
                'data'       => 0,
            ))
            ->getForm();

        $form->bindRequest($request);
        if ($form->isValid()) {
            $me = $this->get('security.context')->getToken()->getUser();
            /* @var $codeManager \PW\InviteBundle\Model\CodeManager */
            $codeManager = $this->get('pw_invite.code_manager');
            $formData    = $form->getData();
            $codeManager->generate($formData['count'], $formData['maxUses'], $me);
            $this->get('session')->setFlash('success', "{$formData['count']} invite code(s) generated successfully.");
        }

        return $this->redirect($this->generateUrl('admin_invite_code_index', array(
            'status' => $status
        )));
    }
}
