<?php

namespace PW\UserBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Form\Type\PartnerEditFormType,
    PW\UserBundle\Document\User,
    PW\UserBundle\Document\Partner;

class PartnerController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/partners/{status}", defaults={"status"="pending"}, name="admin_user_partner_index")
     * @Template
     */
    public function indexAction(Request $request, $status)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();

        $qb = $dm->getRepository('PWUserBundle:Partner')->findByStatus($status);
        $partners = $qb->getQuery()->execute();

        return array(
            'partners' => $partners,
            'status'   => $status,
        );
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/partner/edit/{id}", name="admin_user_partner_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();

        /* @var $partner \PW\PostBundle\Document\Post */
        $partner = $dm->getRepository('PWUserBundle:Partner')->find($id);
        if (!$partner) {
            throw $this->createNotFoundException("Partner not found.");
        }

        $form = $this->createForm(new PartnerEditFormType(), $partner);
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $dm = $this->get('doctrine_mongodb.odm.document_manager');
                $dm->persist($partner);
                $dm->flush();
                $this->get('session')->setFlash('success', 'Partner edited');
                return $this->redirect($this->generateUrl('admin_user_partner_index'));
            }
        }

        return array(
            'partner' => $partner,
            'form'    => $form->createView(),
        );
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/partner/{action}/{id}", name="admin_user_partner_status")
     * @Template
     */
    public function statusAction(Request $request, $action, $id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();

        /* @var $partner \PW\UserBundle\Document\Partner */
        $partner = $dm->getRepository('PWUserBundle:Partner')->find($id);
        if (!$partner) {
            throw $this->createNotFoundException("Partner not found.");
        }

        if ($me instanceOf User) {
            $partner->setStatusUpdatedBy($me);
        }

        $action = strtolower($action);
        switch ($action) {
            case 'approve':
                $form = $this->createFormBuilder()
                    ->add('type', 'choice', array(
                        'choices'   => array('user' => 'User', 'brand' => 'Brand'),
                        'required'  => true,
                    ))
                    ->getForm();
                break;
            case 'reject':
                $form = $this->createFormBuilder()
                    ->add('message', 'textarea', array(
                        'required'  => false,
                    ))
                    ->getForm();
                break;
            case 'unreject':
                $form = $this->createFormBuilder()->getForm();
                break;
        }

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $dm = $this->get('doctrine_mongodb.odm.document_manager');
                switch ($action) {
                    case 'approve':
                        /* @var $userManager \FOS\UserBundle\Document\UserManager */
                        $userManager = $this->get('fos_user.user_manager');
                        /* @var $user \PW\UserBundle\Document\User */
                        $user = $partner->approve($formData['type']);
                        $userManager->updateUser($user);
                        $partner->setUser($user);
                        break;
                    case 'reject':
                        $partner->reject($formData['message']);
                        break;
                    case 'unreject':
                        $partner->setStatus('pending');
                        $partner->setStatusReason(null);
                        break;
                }
                $dm->persist($partner);
                $dm->flush();
                $this->get('session')->setFlash('success', 'Partner status updated');
                return $this->redirect($this->generateUrl('admin_user_partner_index'));
            }
        }

        return array(
            'partner' => $partner,
            'form'    => $form->createView(),
            'action'  => $action,
        );
    }
}
