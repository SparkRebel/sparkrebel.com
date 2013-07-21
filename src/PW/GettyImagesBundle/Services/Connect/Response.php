<?php

namespace PW\GettyImagesBundle\Services\Connect\Message;

use Buzz\Message\Response as BaseResponse;

class Response extends BaseResponse
{
    const API_STATUS_SUCCESS = 'Success';
    const API_STATUS_ERROR   = 'Error';
    const API_STATUS_WARNING = 'Warning';

    private $responseHeader;
    private $responseResult;

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->responseHeader = null;
        $this->responseResult = null;

        if (is_string($content)) {
            $content = json_decode($content, true);

            foreach ($content as $key => $value) {
                if ($key === 'ResponseHeader') {
                    $this->responseHeader = $value;
                } elseif (stripos($key, 'Result') !== false) {
                    $this->responseResult = $value;
                }
            }
        }

        return parent::setContent($content);
    }

    /**
     * @return array
     */
    public function getResponseHeader()
    {
        return (array) $this->responseHeader;
    }

    /**
     * @return array
     */
    public function getResponseResult()
    {
        return (array) $this->responseResult;
    }

    /**
     * Is response successful?
     *
     * @return Boolean
     */
    public function isSuccessful()
    {
        if ($result = parent::isSuccessful()) {
            $responseHeader = $this->getResponseHeader();
            if (!empty($responseHeader['Status'])) {
                switch ($responseHeader['Status']) {
                    case self::API_STATUS_ERROR:
                        $result = false;
                        break;
                    case self::API_STATUS_WARNING:
                    case self::API_STATUS_SUCCESS:
                    default:
                        $result = true;
                        break;
                }
            }
        }

        return $result;
    }

}
