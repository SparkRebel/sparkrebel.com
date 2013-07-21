<?php

namespace PW\GettyImagesBundle\Services\Connect\Message;

use Buzz\Message\Factory\Factory as BaseFactory;
use Buzz\Message\RequestInterface;

class Factory extends BaseFactory
{
    public function createRequest($method = RequestInterface::METHOD_GET, $resource = '/', $host = null)
    {
        return new Request($method, $resource, $host);
    }

    public function createResponse()
    {
        return new Response();
    }
}
