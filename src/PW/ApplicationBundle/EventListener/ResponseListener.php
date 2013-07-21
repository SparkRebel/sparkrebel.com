<?php

namespace PW\ApplicationBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class ResponseListener
{
    /**
     * @var array
     */
    protected $_responseParameters = array(
        'lastModified' => 'setLastModified',
        'statusCode'   => 'setStatusCode',
        'isPublic'     => 'setPublic',
        'isPrivate'    => 'setPrivate',
        'maxAge'       => 'setMaxAge',
        'sharedMaxAge' => 'setSharedMaxAge',
    );

    /**
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request    = $event->getRequest();
        $parameters = $event->getControllerResult();

        if (!is_string($parameters) && !empty($parameters)) {
            $response = array();
            foreach ($parameters as $parameter => $value) {
                if (isset($this->_responseParameters[$parameter])) {
                    $response[$parameter] = $value;
                }
            }

            if (!empty($response)) {
                $request->attributes->set('_response', $response);
            }
        }
    }

    /**
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request    = $event->getRequest();
        $parameters = $request->attributes->get('_response');

        if (!empty($parameters)) {
            $response = $event->getResponse();
            foreach ($parameters as $parameter => $value) {
                $method = $this->_responseParameters[$parameter];
                switch ($method) {
                    case 'setPublic':
                    case 'setPrivate':
                        $response->{$method}();
                        break;
                    default;
                        $response->{$method}($value);
                        break;
                }
            }
        }
    }
}