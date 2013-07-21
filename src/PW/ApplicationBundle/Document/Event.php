<?php

namespace PW\ApplicationBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @MongoDB\Document(collection="eventlog")
 * @MongoDB\Indexes({
 *      @MongoDB\Index(keys={"created"="desc"}, background=true),
 *      @MongoDB\Index(keys={"event"="asc"}, background=true),
 *      @MongoDB\Index(keys={"client"="asc"}, background=true)
 * })
 */
class Event
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $client = 'web';

    /**
     * created
     *
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * createdBy
     *
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $createdBy;

    /**
     * data
     *
     * @MongoDB\Hash
     */
    protected $data;

    /**
     * event
     *
     * @MongoDB\String
     */
    protected $event;

    /**
     * @MongoDB\String
     */
    protected $ip;

    /**
     * @MongoDB\ReferenceMany
     */
    protected $targets;

    public function __construct()
    {
        $this->targets = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set client
     *
     * @param string $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * Get client
     *
     * @return string $client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set created
     *
     * @param date $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
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
     * Set createdBy
     *
     * @param PW\UserBundle\Document\User $createdBy
     */
    public function setCreatedBy(\PW\UserBundle\Document\User $createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * Get createdBy
     *
     * @return PW\UserBundle\Document\User $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set data
     *
     * @param hash $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return hash $data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set event
     *
     * @param string $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @return string $event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set ip
     *
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
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
     * Add targets
     *
     * @param $targets
     */
    public function addTargets($targets)
    {
        $this->targets[] = $targets;
    }

    /**
     * Get targets
     *
     * @return Doctrine\Common\Collections\Collection $targets
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Things to do before inserting
     *
     * @MongoDB\PrePersist
     */
    public function prePersist()
    {
        if (!$this->ip) {
            $remoteIp = remoteIp();
            if ($remoteIp) {
                $this->setIp(ip2long($remoteIp));
            }
        }
    }

}
