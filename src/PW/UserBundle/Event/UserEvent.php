<?php

namespace PW\UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use PW\UserBundle\Document\User;

class UserEvent extends Event
{
    /**
     * @var \PW\UserBundle\Document\User
     */
    protected $user;

    /**
     * @param \PW\UserBundle\Document\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return \PW\UserBundle\Document\User
     */
    public function getUser()
    {
        return $this->user;
    }
}