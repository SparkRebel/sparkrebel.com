<?php

namespace PW\UserBundle\EventListener;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\Security\Core\Exception\AccessDeniedException,
    Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent,
    Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface,
    Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface,
    Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AjaxAuthenticationListener implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    /**
     * Handles security related exceptions.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request   = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            $result = array(
                'success' => false,
                'error'   => $exception->getMessage(),
            );

            if ($exception instanceof AuthenticationException || $exception instanceof AccessDeniedException) {
                $response = new Response(json_encode($result), 403);
            } else {
                $response = new Response(json_encode($result), 500);
            }

            $response->headers->set('Content-Type', 'application/json');
            $event->setResponse($response);
        }
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @see \Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener
     * @param Request        $request
     * @param TokenInterface $token
     * @return Response the response to return
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $targetPath = $request->getSession()->get('_security.target_path');

        if ($request->isXmlHttpRequest()) {
            $user = $token->getUser();
            $signupPreferences = $user->getSettings()->getSignupPreferences();
            $result = array(
                'success' => true,
                'redirect' => !empty($targetPath) ? $targetPath : false,
                'user' => array(
                    'id'       => $user->getId(),
                    'type'     => $user->getType(),
                    'settings' => array(
                        'signup_preferences' => !empty($signupPreferences) ? $signupPreferences : false,
                    ),
                ),
            );
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        return new Response('');
    }

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
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        throw $exception;
    }
}