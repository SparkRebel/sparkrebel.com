<?php

namespace PW\ApiBundle\Response;

use Symfony\Component\HttpKernel\Exception;
use JMS\SerializerBundle\Annotation as API;
use FOS\RestBundle\View\View;

/**
 * @API\ExclusionPolicy("none")
 */
class ApiResponse
{
    /**
     * @var bool
     */
    public $success = false;

    /**
     * @var array
     */
    public $result = array(
        'data'  => array(),
    );

    /**
     * @var array|null
     */
    public $error;

    /**
     * @param mixed $data
     * @param array $extra
     */
    public function __construct($data = null, array $extra = array())
    {
        if ($data !== null) {
            $this->setResult($data, $extra);
        }
    }

    /**
     * @param bool $success
     * @return \PW\ApiBundle\Response\ApiResponse
     */
    public function setSuccess($success = true)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @param mixed $data
     * @param array $extra
     * @return \PW\ApiBundle\Response\ApiResponse
     */
    public function setResult($data, array $extra = array())
    {
        if ($data instanceOf \Doctrine\ODM\MongoDB\EagerCursor) {
            $data = iterator_to_array($data, false);
        }

        foreach ($data as $key => $value) {
            if ($value instanceOf \Doctrine\ODM\MongoDB\EagerCursor) {
                $data[$key] = iterator_to_array($value, false);
            }
        }

        $this->success = true;
        $this->result['data'] = $data;

        foreach ($extra as $key => $value) {
            switch ($key) {
                case 'total':
                case 'page':
                case 'limit':
                case 'next':
                    $this->result[$key] = $value;
                    break;
            }
        }

        return $this;
    }

    /**
     * @param string $message
     * @param int $code
     * @return \PW\ApiBundle\Response\ApiResponse
     */
    public function setError($message, $code)
    {
        $this->success = false;
        $this->result  = null;
        $this->error   = array(
            'message' => $message,
            'code'    => $code,
        );

        $response = View::create()
            ->setStatusCode($this->error['code'])
            ->setData($this);

        return $response;
    }
}