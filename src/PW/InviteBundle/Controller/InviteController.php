<?php

namespace PW\InviteBundle\Controller;

use PW\ApplicationBundle\Controller\AbstractController,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    PW\InviteBundle\Form\Model\CreateInviteRequest,
    PW\InviteBundle\Form\Type\CreateInviteRequestType,
    PW\InviteBundle\Document\Request as InviteRequest,
    PW\InviteBundle\Form\Model\RedeemCode,
    PW\InviteBundle\Form\Type\RedeemCodeType,
    PW\InviteBundle\Document\Code;

class InviteController extends AbstractController
{
    /**
     * @Route("/request_invite", name="invite_request")
     * @Template
     */
    public function requestAction(Request $request)
    {
        /* @var $requestManager \PW\InviteBundle\Model\RequestManager */
        $requestManager = $this->get('pw_invite.request_manager');
        $result         = array('success' => false);

        $form = $this->createForm(
            new CreateInviteRequestType(),
            new CreateInviteRequest()
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $requestManager->update($formData->getInviteRequest());
                $result = array('success' => true);
                $this->get('session')->setFlash('success', "Thank you - we have received your invitation request.");
            } else {
                $result['error'] = $this->_getFirstErrorMessage($form);
            }

            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $result = array('form' => $form->createView());
        return $result;
    }

    /**
     * @Route("/redeem/{id}", requirements={"id"="[\da-f]{24}"}, name="invite_redeem_id")
     * @Route("/redeem/{value}", defaults={"value"=null}, name="invite_redeem_code")
     * @Template
     */
    public function redeemAction(Request $request, $value = null, $id = null)
    {
        /* @var $codeManager \PW\InviteBundle\Model\CodeManager */
        $codeManager = $this->get('pw_invite.code_manager');
        $result      = array('success' => false);

        $code = null;
        if ($id) {
            $code = $codeManager->find($id);
        } elseif ($value) {
            $code = $codeManager->findByValue($value);
        }

        $form = $this->createForm(
            new RedeemCodeType(),
            new RedeemCode($code)
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $value    = $formData->getCode()->getValue();
                $code     = $codeManager->findByValue($value);
                if ($code) {
                    $this->get('session')->set('invite_code_id', $code->getId());
                    $result = array('success' => true);
                } else {
                    $result['error'] = "You've entered an invalid code.";
                }
            } else {
                $result['error'] = $this->_getFirstErrorMessage($form);
            }

            if ($request->isXmlHttpRequest()) {
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            } else {
                if ($result['success']) {
                    $this->get('session')->setFlash('success', '');
                } else {
                    $this->get('session')->setFlash('error', '');
                }
            }
        }

        $result = array('form' => $form->createView());
        return $result;
    }
}
