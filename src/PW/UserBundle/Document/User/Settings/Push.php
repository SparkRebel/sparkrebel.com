<?php

namespace PW\UserBundle\Document\User\Settings;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo,
    PW\ApplicationBundle\Document\AbstractDocument;

/**
 * @MongoDB\EmbeddedDocument
 */
class Push extends AbstractDocument
{
    /**
     * @var string
     * @MongoDB\String
     */
    protected $apns;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $c2dm;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $mpns;

    /**
     * @var bool
     * @MongoDB\NotSaved
     */
    protected $isActive;

    /**
     * @var bool
     * @MongoDB\NotSaved
     */
    protected $deleted;


    /**
     * @return string
     */
    public function getAdminValue()
    {
        return null;
    }

    //
    // Doctrine Generation Below
    //

    /**
     * Set apns
     *
     * @param string $apns
     */
    public function setApns($apns)
    {
        $this->apns = $apns;
    }

    /**
     * Get apns
     *
     * @return string $apns
     */
    public function getApns()
    {
        return $this->apns;
    }

    /**
     * Set c2dm
     *
     * @param string $c2dm
     */
    public function setC2dm($c2dm)
    {
        $this->c2dm = $c2dm;
    }

    /**
     * Get c2dm
     *
     * @return string $c2dm
     */
    public function getC2dm()
    {
        return $this->c2dm;
    }

    /**
     * Set mpns
     *
     * @param string $mpns
     */
    public function setMpns($mpns)
    {
        $this->mpns = $mpns;
    }

    /**
     * Get mpns
     *
     * @return string $mpns
     */
    public function getMpns()
    {
        return $this->mpns;
    }

    /**
     * Set isActive
     *
     * @param string $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Get isActive
     *
     * @return string $isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set deleted
     *
     * @param date $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Get deleted
     *
     * @return date $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
    /**
     * @var date $created
     */
    protected $created;

    /**
     * @var date $modified
     */
    protected $modified;


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
     * Set modified
     *
     * @param date $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * Get modified
     *
     * @return date $modified
     */
    public function getModified()
    {
        return $this->modified;
    }
}
