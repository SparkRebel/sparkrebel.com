<?php

namespace PW\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\ApiBundle\Form\Type\CreateClientType,
    PW\ApiBundle\Form\Model\CreateClient;

class ClientController extends Controller
{
    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/api/client/request", name="api_client_request")
     * @Template
     */
    public function requestAction(Request $request)
    {
        /* @var $clientManager \PW\ApiBundle\Model\ClientManager */
        $clientManager = $this->get('pw_api.client_manager');
        $result        = array('success' => false);

        /* @var $me \PW\UserBundle\Document\User */
        $me   = $this->get('security.context')->getToken()->getUser();
        $form = $this->createForm(
            new CreateClientType(),
            new CreateClient($me)
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $clientManager->updateClient($formData->getClient());
                $result = array('success' => true);
            } else {
                $result['error'] = $this->_getFirstErrorMessage($form);
            }

            if ($request->isXmlHttpRequest()) {
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            } else {
                if ($result['success']) {
                    $this->get('session')->setFlash('success', 'API access request submitted successfully');
                    return $this->redirect($this->generateUrl('api_client_details'));
                } else {
                    $this->get('session')->setFlash('error', 'There was an error with the information you\'ve entered');
                }
            }
        }

        $result = array(
            'form'  => $form->createView(),
            'title' => 'Request API Access',
        );

        return $result;
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/api/client/details", name="api_client_details")
     * @Template
     */
    public function detailsAction(Request $request)
    {
        /* @var $clientManager \PW\ApiBundle\Model\ClientManager */
        $clientManager = $this->get('pw_api.client_manager');

        /* @var $me \PW\UserBundle\Document\User */
        $me = $this->get('security.context')->getToken()->getUser();

        /* @var $client \PW\ApiBundle\Document\Client */
        $client = $clientManager->getRepository()
            ->createQueryBuilder()
            ->field('user')->references($me)
            ->field('user')->prime(false)
            ->getQuery()->getSingleResult();

        return array(
            'client' => $client
        );
    }
}
