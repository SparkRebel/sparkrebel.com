<?php

namespace PW\GettyImagesBundle\Services\Connect\Message;

use Buzz\Message\Request as BaseRequest;

class Request extends BaseRequest
{
    protected $type;
    protected $endpoint;

    protected $requestHeader = array(
        'Token'          => '',
        'CoordinationId' => '',
    );

    protected $requestBodyKey;
    protected $requestBody;

    public function __construct()
    {
        $this->method   = self::METHOD_POST;
        $this->resource = "/v1/{$this->type}/{$this->endpoint}";
        $this->host     = 'https://connect.gettyimages.com';

        $this->addHeaders(array('Content-Type' => 'application/json'));
    }

    public function getContent()
    {
        if (empty($this->content)) {
            $this->content = array();
            $this->content['RequestHeader'] = $this->requestHeader;
            $this->content["{$this->endpoint}RequestBody"] = $this->requestBody;
        }

        if (is_array($this->content)) {
            $this->content = json_encode($this->content);
        }

        return parent::getContent();
    }
}
