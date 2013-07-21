<?php

namespace PW\GettyImagesBundle\Services\Connect\Request;

use PW\GettyImagesBundle\Services\Connect\Request;

class CreateSessionRequest extends Request
{
    protected $type     = 'session';
    protected $endpoint = 'CreateSession';

    protected $requestBody = array(
        'SystemId'       => '',
        'SystemPassword' => '',
        'UserName'       => '',
        'UserPassword'   => '',
        'RememberedUser' => true,
    );

    public function setSystemId($systemId)
    {
        $this->requestBody['SystemId'] = $systemId;
    }

    public function setSystemPassword($systemPassword)
    {
        $this->requestBody['SystemPassword'] = $systemPassword;
    }

    public function setUserName($userName)
    {
        $this->requestBody['UserName'] = $userName;
    }

    public function setUserPassword($userPassword)
    {
        $this->requestBody['UserPassword'] = $userPassword;
    }
}
