<?php

namespace PW\ApiBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\ApiBundle\Document\Client,
    PW\UserBundle\Document\User;

class CreateClient
{
    /**
     * @Assert\Type(type="PW\ApiBundle\Document\Client")
     * @Assert\Valid
     */
    protected $client;

    /**
     * @Assert\Type(type="PW\UserBundle\Document\User")
     * @Assert\Valid
     */
    protected $user;

    /**
     * @param \PW\ApiBundle\Document\Client $client
     */
    public function __construct(User $user, Client $client = null)
    {
        $this->user   = $user;
        $this->client = $client;
    }

    /**
     * @param \PW\ApiBundle\Document\Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        $this->client->setUser($this->user);
    }

    /**
     * @return \PW\ApiBundle\Document\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return \PW\UserBundle\Document\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
