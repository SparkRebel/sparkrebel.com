<?php

namespace PW\InviteBundle\EventListener;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface,
    PW\InviteBundle\Security\Exception\InviteOnlyException;

class InviteOnlyListener implements AuthenticationFailureHandlerInterface
{
    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     * @return Response the response to return
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $result = array('success' => false, 'message' => $exception->getMessage());
            if ($exception instanceOf InviteOnlyException) {
                $response = new Response(json_encode($result), 503);
            } else {
                $response = new Response(json_encode($result));
            }
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if ($targetPath = $request->getSession()->get('_security.target_path')) {
            return new RedirectResponse($targetPath);
        }

        throw new $exception;
    }
}