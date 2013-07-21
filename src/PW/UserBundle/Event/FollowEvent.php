<?php

namespace PW\UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use PW\UserBundle\Document\Follow;

class FollowEvent extends Event
{
    /**
     * @var \PW\UserBundle\Document\Follow
     */
    protected $follow;

    /**
     * @param \PW\UserBundle\Document\Follow
     */
    public function __construct(Follow $follow)
    {
        $this->follow = $follow;
    }

    /**
     * @return \PW\UserBundle\Document\Follow
     */
    public function getFollow()
    {
        return $this->follow;
    }
}