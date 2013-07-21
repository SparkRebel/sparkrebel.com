<?php

namespace PW\StatsBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @MongoDB\Document(collection="Stats.detailed")
 */
class Stats
{
    /**
     *  @MongoDB\Id
     */ 
    protected $id;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $user;

    /**
     *  @MongoDB\String
     */
    protected $action;

    /**
     * @MongoDB\ReferenceOne(cascade={"persist"})
     */
    protected $reference;

    /**
     *  @MongoDB\Int
     */
    protected $ip;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * Set user
     *
     * @param PW\UserBundle\Document\User $user
     * @return Stats
     */
    public function setUser(\PW\UserBundle\Document\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return PW\UserBundle\Document\User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Stats
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get action
     *
     * @return string $action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return Stats
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * Get ip
     *
     * @return string $ip
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set created
     *
     * @param date $created
     * @return Stats
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return date $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set reference
     *
     * @param $reference
     * @return Stats
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Get reference
     *
     * @return $reference
     */
    public function getReference()
    {
        return $this->reference;
    }

}
