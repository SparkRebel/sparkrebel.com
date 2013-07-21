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
    PW\InviteBundle\Document\Request as InviteRequest;

class RequestController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/invite/requests/{status}", defaults={"status"="pending"}, name="admin_invite_request_index")
     * @Template
     */
    public function indexAction(Request $request, $status)
    {
        /* @var $requestManager \PW\InviteBundle\Model\RequestManager */
        $requestManager = $this->get('pw_invite.request_manager');
        $qb = $requestManager->getRepository()->findByStatus($status);

        /* @var $paginator \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        return array(
            'requests' => $pagination,
            'status'   => $status,
        );
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/invite/request/mass", name="admin_invite_request_mass")
     */
    public function massAction(Request $request)
    {
        /* @var $requestManager \PW\InviteBundle\Model\RequestManager */
        $requestManager = $this->get('pw_invite.request_manager');

        $postData = $request->request;
        if (!$postData->has('massaction') || !$postData->has('requests')) {
            throw new \Symfony\Component\Routing\Exception\MissingMandatoryParametersException();
        }

        $requestIds = $postData->get('requests');
        $requests   = $requestManager->findByIds($requestIds);
        if ($requests->count() < 1) {
            throw $this->createNotFoundException("No Invite Requests found");
        }

        /* @var $codeManager \PW\InviteBundle\Model\CodeManager */
        $codeManager = $this->get('pw_invite.code_manager');

        $me = $this->get('security.context')->getToken()->getUser();
        switch ($postData->get('massaction')) {

            case 'assign_new_random':
                foreach ($requests as $inviteRequest) {
                    $code = $codeManager->createRandom(1, $me);
                    $code = $codeManager->update($code);
                    $requestManager->assignCode($inviteRequest, $code, $me);
                }
                $this->get('session')->setFlash('success', sprintf(
                    "Successfully assigned new random codes to %d invite request(s)", $requests->count()
                ));
                return $this->redirect($this->generateUrl('admin_invite_request_index', array(
                    'status' => 'assigned'
                )));
                break;

            case 'assign_new_custom':
                if ($request->getMethod() == 'POST') {
                    if (!$postData->has('code')) {
                        throw new \Symfony\Component\Routing\Exception\MissingMandatoryParametersException();
                    }
                    $code = $codeManager->create(array(
                        'createdBy' => $me,
                        'value'     => $postData->get('code'),
                        'type'      => 'custom',
                        'maxUses'   => count($requests),
                    ));
                    $code = $codeManager->update($code);
                    foreach ($requests as $inviteRequest) {
                        $requestManager->assignCode($inviteRequest, $code, $me);
                    }
                    $this->get('session')->setFlash('success', sprintf(
                        "Successfully assigned code '%s' to %d invite request(s)", $code->getValue(), $requests->count()
                    ));
                    return $this->redirect($this->generateUrl('admin_invite_request_index', array(
                        'status' => 'assigned'
                    )));
                }
                return $this->render('PWInviteBundle:Admin\\Request:massAssign.html.twig', array(
                    'postData' => $postData,
                ));
                break;

            case 'delete':
                foreach ($requests as $inviteRequest) {
                    $requestManager->delete($inviteRequest, $me);
                }
                $this->get('session')->setFlash('success', sprintf(
                    "Successfully deleted %d invite request(s)", $requests->count()
                ));
                break;

            default:
                throw new \Symfony\Component\Routing\Exception\InvalidParameterException();
                break;
        }

        return $this->redirect($this->generateUrl('admin_invite_request_index'));
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/invite/request/delete/{id}", name="admin_invite_request_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        /* @var $requestManager \PW\InviteBundle\Model\RequestManager */
        $requestManager = $this->get('pw_invite.request_manager');

        /* @var $inviteRequest \PW\InviteBundle\Document\Request */
        $inviteRequest = $requestManager->getRepository()->find($id);
        if (!$inviteRequest) {
            throw $this->createNotFoundException("Invite Request not found");
        }

        $me = $this->get('security.context')->getToken()->getUser();
        $requestManager->delete($inviteRequest, $me);

        $this->get('session')->setFlash('success', sprintf(
            "Successfully deleted request from e-mail '%s'", $inviteRequest->getEmail()
        ));
        return $this->redirect($this->generateUrl('admin_invite_request_index'));
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/invite/request/send/{id}", name="admin_invite_request_assign")
     * @Template
     */
    public function assignAction(Request $request, $id)
    {
        /* @var $requestManager \PW\InviteBundle\Model\RequestManager */
        $requestManager = $this->get('pw_invite.request_manager');

        /* @var $inviteRequest \PW\InviteBundle\Document\Request */
        $inviteRequest = $requestManager->getRepository()->find($id);
        if (!$inviteRequest) {
            throw $this->createNotFoundException("Invite Request not found");
        }

        /* @var $codeManager \PW\InviteBundle\Model\CodeManager */
        $codeManager = $this->get('pw_invite.code_manager');

        $form = $this->createFormBuilder()
            ->add('type', 'choice', array(
                'choices'  => array(
                    'random' => 'New Random Code',
                    'custom' => 'New Custom Code',
                ),
                'required' => true,
            ))
            ->add('code', 'text', array(
                'required' => false
            ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $me = $this->get('security.context')->getToken()->getUser();
                $formData = $form->getData();
                switch ($formData['type']) {
                    case 'random':
                        $code = $codeManager->createRandom(1, $me);
                        break;
                    case 'custom':
                        $code = $codeManager->create(array(
                            'createdBy' => $me,
                            'value'     => $formData['code'],
                            'type'      => 'custom',
                            'maxUses'   => 1,
                        ));
                        break;
                }
                $code = $codeManager->update($code);
                $requestManager->assignCode($inviteRequest, $code, $me);
                $this->get('session')->setFlash('success', sprintf(
                    "Successfully assigned code '%s' to e-mail '%s'", $code->getValue(), $inviteRequest->getEmail()
                ));
                return $this->redirect($this->generateUrl('admin_invite_request_index', array(
                    'status' => 'assigned'
                )));
            }
        }

        return array(
            'inviteRequest' => $inviteRequest,
            'form'          => $form->createView(),
        );
    }
}
