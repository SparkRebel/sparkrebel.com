<?php

namespace PW\ApiBundle\Model;

use FOS\OAuthServerBundle\Document\ClientManager as BaseClientManager;

class ClientManager extends BaseClientManager
{
    /**
     * @return \PW\ApiBundle\Repository\ClientRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
